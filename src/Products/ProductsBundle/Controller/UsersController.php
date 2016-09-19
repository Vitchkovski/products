<?php

namespace Products\ProductsBundle\Controller;

use Products\ProductsBundle\Entity\User;
use Products\ProductsBundle\Form\PasswordRecoverType;
use Products\ProductsBundle\ProductsProductsBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Products\ProductsBundle\Form\Type\RegistrationType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class UsersController extends Controller
{
    public function registerAction(Request $request)
    {
        // 1) build the form
        $registration = new User();

        $form = $this->createForm(RegistrationType::class, $registration);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            // 3) Encode the password
            $pwd = $user->getPassword();
            $encoder = $this->container->get('security.password_encoder');
            $pwd = $encoder->encodePassword($user, $pwd);
            $user->setPassword($pwd);

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //5) Log in user right after creation
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            //6) Redirecting to the personal page
            return $this->redirectToRoute('ProductsProductsBundle_userPersonalPage');
        }
        $errors = $this->getErrorsAsArray($form);

        return $this->render(
            'ProductsProductsBundle:Login:register.html.twig',
            array('errors' => $errors, 'form' => $form->createView())
        );


    }

    public function passwordRecoveryAction(Request $request)
    {


        $form = $this->createForm(\Products\ProductsBundle\Form\Type\PasswordRecoverType::class);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form["email"]->getData();

            //we must check first if user with such email exists in the DB
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('ProductsProductsBundle:User')->findOneBy(array('email' => $email));

            if (!$user) {
                //there is no such user. Show error.
                $this->addFlash(
                    'notice',
                    'User with this email does not exist in the database.');

                return $this->redirectToRoute('ProductsProductsBundle_restorePassword');
            }


            //sending email...
            //Generating security field
            $username = $user->getUsername();
            $emailResetLinkCode = sha1($username.'1ws65$ngU'.uniqid(rand(), true));

            //saving security filed to the user record
            $user->setHashKey($emailResetLinkCode);
            $em->persist($user);
            $em->flush();

            //preparing message
            $message = \Swift_Message::newInstance()
                ->setSubject('Reset your Email')
                ->setFrom('mail@vitchkovski.com', 'Vitchkovski')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        '@ProductsProducts/Templates/resetPasswordEmail.html.twig',
                        array('name' => $username, 'security_code' => $emailResetLinkCode)
                    ),
                    'text/html');

            //send
            $this->get('mailer')->send($message);

        }


        return $this->render('ProductsProductsBundle:Login:passwordRecovery.html.twig',
            array('form' => $form->createView()));
    }

    public function passwordResetAction($resetEmailCode, Request $request)
    {
        //first we have to confirm if reset code is correct
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('ProductsProductsBundle:User')->findOneBy(array('hash_key' => $resetEmailCode));

        if(!$user){
            //code is incorrect, notifying user
            $this->addFlash(
                'notice',
                'There was an error when resetting your email. Please try again.');

            return $this->redirectToRoute('ProductsProductsBundle_restorePassword');
        }

        //if everything is correct we open form to enter new password
        $form = $this->createForm(\Products\ProductsBundle\Form\Type\SubmitNewPasswordType::class);

        //if form was submitted it means we have to update current password with a new one
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Encode the password
            $password = $form["password"]->getData();
            $encoder = $this->container->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $password);
            $user->setPassword($password);

            // save the User
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //Log in user right after creation
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            //Redirecting to the personal page
            return $this->redirectToRoute('ProductsProductsBundle_userPersonalPage');

        }

        return $this->render('ProductsProductsBundle:Login:passwordUpdate.html.twig',
            array('form' => $form->createView(),
                'security_code' => $resetEmailCode));

    }

    public function getErrorsAsArray($form)
    {
        //getting form validation array as an array for further handling
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $key => $child) {
            if ($err = $this->getErrorsAsArray($child)) {
                $errors[$key] = $err;
            }
        }

        return $errors;
    }


}