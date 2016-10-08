<?php

namespace Vitchkovski\ProductsBundle\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\DependencyInjection\ContainerInterface;






class UsersService extends Controller
{
    private $em;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function saveUserToDB($form)
    {
        $user = $form->getData();

        // 3) Encode the password
        $pwd = $user->getPassword();
        dump($pwd);
        $encoder = $this->container->get('security.password_encoder');
        $pwd = $encoder->encodePassword($user, $pwd);
        $user->setPassword($pwd);

        //4) Creating apiKey
        $apiKey = md5($user->getUsername() . '1ws65$ngU');
        $user->setApiKey($apiKey);

        //5) Saving user
        $this->em->persist($user);
        $this->em->flush();

        //6) Return user to the controller
        return $user;
    }

}