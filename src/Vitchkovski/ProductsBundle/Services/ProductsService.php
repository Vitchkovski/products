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






class ProductsService extends Controller
{
    private $em;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function saveCreateProductToDB($form, $user)
    {
        $product = $form->getData();


        //Checking if file was submitted
        $file = $product->getProductImgName();
        if ($file) {
            //starting general processing process for uploaded images.
            //Generating new image name, cropping image, moving both original and cropped images to the user's folder.
            $fileName = $this->get('app.image_uploader')->upload($file, $user->getUserId());

            //saving product image name
            $product->setProductImgName($fileName);
        }


        //setting user info for a new product
        $product->setUser($user);

        //retrieving submitted categories
        $categories = $product->getCategories();

        foreach ($categories as $category) {
            if ($category->getCategoryName() !== null) {
                //for each category submitted we must set product reference
                $category->setProduct($product);
            } else {
                //if null category was submitted - we don't need to save it in the DB
                $product->removeCategory($category);
            }
        }

        //saving changes to the DB
        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    public function saveEditProductToDB($form, $user, $productImg)
    {
        $product = $form->getData();
        $categories = $product->getCategories();

        //Checking if image was submitted
        $file = $product->getProductImgName();
        if ($file) {
            //starting general processing process for uploaded images.
            //Generating new image name, cropping image, moving both original and cropped images to the user's folder.
            $fileName = $this->get('app.image_uploader')->upload($file, $user->getUserId());

            //saving new image name
            $product->setProductImgName($fileName);

        } else {

            //saving original image name (whether it is null or not)
            $product->setProductImgName($productImg);
        }


        foreach ($categories as $category) {
            if ($category->getCategoryName() !== null) {
                //for each category submitted we must set product reference
                $category->setProduct($product);
            } else {
                //if null category was submitted we have to delete it from the DB
                $this->getDoctrine()->getManager()
                    ->remove($category);
            }
        }

        //saving changes
        $this->em->flush();
    }

}