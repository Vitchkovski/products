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