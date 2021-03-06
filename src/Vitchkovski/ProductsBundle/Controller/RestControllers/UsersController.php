<?php

namespace Vitchkovski\ProductsBundle\Controller\RestControllers;

use Vitchkovski\ProductsBundle\Entity\User;
use Vitchkovski\ProductsBundle\Form\PasswordRecoverType;
use Vitchkovski\ProductsBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Vitchkovski\ProductsBundle\Form\Type\RegistrationType;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\JsonResponse;


class UsersController extends FOSRestController
{

    //get user API
    //curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/users/1
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

    //get users api action
    //curl -H "X-AUTH-TOKEN: ee8cc5f8cf56e99c366bcc2361f0031f" http://vitchkovski.com/api/users
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
    //curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/users/me
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
    //curl -v -H "Accept: application/json" -H "Content-type: application/json" POST -d "{\"registration\": {\"username\":\"foo3\", \"email\": \"foo3@example.org\", \"password\": \"123456\"}}" http://vitchkovski.com/api/users/new
    public function getUsersNewAction(Request $request)
    {

        // 1) build the form
        $registration = new User();

        $form = $this->createForm(RegistrationType::class, $registration);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);


        if ($form->isValid()) {

            //general process for saving user to the DB
            $user = $this->get('app.users_service')->saveUserToDB($form);

            return new View($user, Response::HTTP_CREATED);
        }

        return View::create($form, 400);
    }

    //get User API key
    //curl -v -H "Accept: application/json" -H "Content-type: application/json" POST -d "{\"user\": {\"email\":\"lsa15gmail.com\", \"password\": \"123123\"}}" http://vitchkovski.com/api/users/key
    public function getUsersKeyAction(Request $request)
    {

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        $user = $form->getData();

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


        }

        return View::create($form, 400);

    }

    //API to submit password recovery email
    //curl -v -H "Accept: application/json" -H "Content-type: application/json" POST -d "{\"password_recovery\": {\"email\": \"lsa15@gmail.com\"}}" http://vitchkovski.com/api/users/password/recovery
    public function getUsersPasswordRecoveryAction(Request $request)
    {
        //General process of password resetting. Showing form to submit email, sending email
        $form = $this->createForm(\Vitchkovski\ProductsBundle\Form\Type\PasswordRecoverType::class);

        $form->handleRequest($request);
        if ($form->isValid()) {

            //we must check first if user with such email exists in the DB
            $user = $this->getDoctrine()->getManager()
                ->getRepository('VitchkovskiProductsBundle:User')
                ->findOneBy(array('email' => $form["email"]->getData()));

            if (!$user) {
                //there is no such user. Show error.
                return new JsonResponse('Email not found in the DB', 401);
            }

            $this->get('app.users_service')->sendPasswordRecoveryEmail($form, $user);

/*
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
                ->setFrom('mail@vitchkovski.com', 'Vitchkovski1')
                ->setTo($email)
                ->setBody(
                    $this->renderView(
                        '@VitchkovskiProducts/Templates/resetPasswordEmail.html.twig',
                        array('name' => $username, 'security_code' => $emailResetLinkCode)
                    ),
                    'text/html');

            //send
            $this->get('mailer')->send($message);*/

        }

        return View::create($form, 400);
    }

}