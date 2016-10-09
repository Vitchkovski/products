<?php

namespace Vitchkovski\ProductsBundle\Controller;

use Vitchkovski\ProductsBundle\Entity\User;
use Vitchkovski\ProductsBundle\Form\PasswordRecoverType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Vitchkovski\ProductsBundle\Form\Type\RegistrationType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use FOS\RestBundle\Controller\Annotations as Rest;


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

            //general process for saving user to the DB
            $user = $this->get('app.users_service')->saveUserToDB($form);

            //Log in user right after creation
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            // Redirecting to the personal page
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

            //we must check first if user with such email exists in the DB
            $user = $this->getDoctrine()->getManager()
                ->getRepository('VitchkovskiProductsBundle:User')
                ->findOneBy(array('email' => $form["email"]->getData()));

            if (!$user) {
                //there is no such user. Show error.
                $this->addFlash(
                    'notice',
                    'User with this email does not exist in the database.');

                return $this->redirectToRoute('VitchkovskiProductsBundle_restorePassword');
            }

            //general process to send an email
            $this->get('app.users_service')->sendPasswordRecoveryEmail($form, $user);

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

    public function showUserInfoAction()
    {
        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('VitchkovskiProductsBundle:Login:userInfo.html.twig',
            array('user' => $user));
    }


}