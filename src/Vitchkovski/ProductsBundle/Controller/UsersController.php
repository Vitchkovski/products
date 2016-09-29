<?php

namespace Vitchkovski\ProductsBundle\Controller;

use Vitchkovski\ProductsBundle\Entity\User;
use Vitchkovski\ProductsBundle\Form\PasswordRecoverType;
use Vitchkovski\ProductsBundle\Form\UserType;
use Vitchkovski\ProductsBundle\VitchkovskiProductsBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Vitchkovski\ProductsBundle\Form\Type\RegistrationType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;


class UsersController extends FOSRestController
{
    public function registerAction(Request $request)
    {
        // 1) build the form
        $registration = new User();

        $form = $this->createForm(RegistrationType::class, $registration);
        dump($request);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            // 3) Encode the password
            $pwd = $user->getPassword();
            $encoder = $this->container->get('security.password_encoder');
            $pwd = $encoder->encodePassword($user, $pwd);
            $user->setPassword($pwd);

            //3.5) Creating apiKey
            $apiKey = md5($user->getUsername() . '1ws65$ngU');
            $user->setApiKey($apiKey);
            dump($apiKey);

            // 4) save the User!
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            //5) Log in user right after creation
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            //6) Redirecting to the personal page
            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
        }


        return $this->render(
            'VitchkovskiProductsBundle:Login:register.html.twig',
            array('form' => $form->createView())
        );


    }

    public function passwordRecoveryAction(Request $request)
    {
        //General process of password resetting. Showing form to submit email, sending email
        $form = $this->createForm(\Vitchkovski\ProductsBundle\Form\Type\PasswordRecoverType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form["email"]->getData();

            //we must check first if user with such email exists in the DB
            $user = $this->getDoctrine()->getManager()
                ->getRepository('VitchkovskiProductsBundle:User')
                ->findOneBy(array('email' => $email));

            if (!$user) {
                //there is no such user. Show error.
                $this->addFlash(
                    'notice',
                    'User with this email does not exist in the database.');

                return $this->redirectToRoute('VitchkovskiProductsBundle_restorePassword');
            }


            //sending email...
            //Generating security field
            $username = $user->getUsername();
            $emailResetLinkCode = sha1($username . '1ws65$ngU' . uniqid(rand(), true));

            //saving security filed to the user record
            $user->setHashKey($emailResetLinkCode);
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            //preparing message
            $message = \Swift_Message::newInstance()
                ->setSubject('Reset your Email')
                ->setFrom('mail@vitchkovski.com', 'Vitchkovski')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        '@VitchkovskiProducts/Templates/resetPasswordEmail.html.twig',
                        array('name' => $username, 'security_code' => $emailResetLinkCode)
                    ),
                    'text/html');

            //send
            $this->get('mailer')->send($message);

            $this->addFlash(
                'notice',
                'Email to reset your password has been sent.');

        }


        return $this->render('VitchkovskiProductsBundle:Login:passwordRecovery.html.twig',
            array('form' => $form->createView()));
    }

    public function passwordResetAction($resetEmailCode, Request $request)
    {
        //first we have to confirm if reset code is correct
        $user = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:User')
            ->findOneBy(array('hash_key' => $resetEmailCode));

        if (!$user) {
            //code is incorrect, notifying user
            $this->addFlash(
                'notice',
                'There was an error when resetting your email. Please try again.');

            return $this->redirectToRoute('VitchkovskiProductsBundle_restorePassword');
        }

        //if everything is correct we open form to enter new password
        $form = $this->createForm(\Vitchkovski\ProductsBundle\Form\Type\SubmitNewPasswordType::class);

        //if form was submitted it means we have to update current password with a new one
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Encode the password
            $password = $form["password"]->getData();
            $encoder = $this->container->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $password);
            $user->setPassword($password);

            // save the User
            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            //Log in user right after creation
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            //Redirecting to the personal page
            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');

        }

        return $this->render('VitchkovskiProductsBundle:Login:passwordUpdate.html.twig',
            array('form' => $form->createView(),
                'security_code' => $resetEmailCode));

    }

    /**
     * @Rest\View
     */
    public function getUserAction($id)
    {

        $user = $this
            ->getDoctrine()
            ->getRepository('VitchkovskiProductsBundle:User')
            ->findBy(array('user_id' => $id));

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $encoders = new JsonEncoder();
        $normalizers = new ObjectNormalizer();
        $normalizers->setIgnoredAttributes(array('products', 'password', 'salt', 'hashKey'));


        $serializer = new Serializer(array($normalizers), array($encoders));

        $jsonContent = $serializer->serialize($user, 'json');

        return $jsonContent;
    }

    //get user api action
    public function getUsersAction()
    {
        $users = $this
            ->getDoctrine()
            ->getRepository('VitchkovskiProductsBundle:User')
            ->findAll();

        $encoders = new JsonEncoder();
        $normalizers = new ObjectNormalizer();
        $normalizers->setIgnoredAttributes(array('products', 'password', 'salt', 'hashKey'));


        $serializer = new Serializer(array($normalizers), array($encoders));

        $jsonContent = $serializer->serialize($users, 'json');

        return $jsonContent;
    }

    //get info for the logged user api action
    public function getUsersMeAction()
    {
        if (!is_object($this->getUser())) {
            throw new AccessDeniedException();
        }

        $user = $this->getUser();

        $encoders = new JsonEncoder();
        $normalizers = new ObjectNormalizer();
        $normalizers->setIgnoredAttributes(array('products'));


        $serializer = new Serializer(array($normalizers), array($encoders));

        $jsonContent = $serializer->serialize($user, 'json');

        return $jsonContent;

    }

    //create new user with api
    public function getUsersNewAction(Request $request)
    {

        // 1) build the form
        $registration = new User();

        $form = $this->createForm(RegistrationType::class, $registration);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);


        if ($form->isValid()) {

            $user = $form->getData();

            // 3) Encode the password
            $pwd = $user->getPassword();
            $encoder = $this->container->get('security.password_encoder');
            $pwd = $encoder->encodePassword($user, $pwd);
            $user->setPassword($pwd);


            $this->getDoctrine()->getManager()->persist($user);
            $this->getDoctrine()->getManager()->flush();

            return new View($user, Response::HTTP_CREATED);
        }

        return View::create($form, 400);
    }

    //login action with API
    public function getUsersKeyAction(Request $request)
    {

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        $user = $form->getData();
        //dump($user);

        if ($form->isValid()) {

            //$pwd = $form->getData()->getPassword();
            $encoder = $this->container->get('security.password_encoder');
            $pwd = $encoder->encodePassword($user, $user->getPassword());

            $user = $this->getDoctrine()->getManager()
                ->getRepository('VitchkovskiProductsBundle:User')
                ->loadUserByUsername($form->getData()->getEmail());


            if (!$user || $user->getPassword()!= $pwd) {
                return new JsonResponse('Credentials are incorrect', 401);
            }

            $encoders = new JsonEncoder();
            $normalizers = new ObjectNormalizer();

            $serializer = new Serializer(array($normalizers), array($encoders));

            $jsonContent = $serializer->serialize($user->getApiKey(), 'json');

            return $jsonContent;

            return $user;

        }
        /*$user = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:User')
            ->findOneBy(array('hash_key' => $resetEmailCode));

        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();*/

        return View::create($form, 400);

        /*$this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));*/

    }


}