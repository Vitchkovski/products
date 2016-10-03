<?php

namespace Vitchkovski\ProductsBundle\Controller\RestControllers;

use FOS\RestBundle\Controller\FOSRestController;
use Vitchkovski\ProductsBundle\Entity\Category;
use Vitchkovski\ProductsBundle\Entity\Product;
use Vitchkovski\ProductsBundle\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;


class ProductsController extends FOSRestController
{
    //get product info API
    //curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/products/1
    public function getProductAction($id)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $product = $this
            ->getDoctrine()
            ->getRepository('VitchkovskiProductsBundle:Product')
            ->findBy(array('product_id' => $id, 'user' => $user->getUserId()));

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $encoders = new JsonEncoder();
        $normalizers = new ObjectNormalizer();

        //circular reference must be handled
        $normalizers->setCircularReferenceHandler(function ($category) {
            return $category->getCategories();
        });

        $normalizers->setIgnoredAttributes(array('user'));

        $serializer = new Serializer(array($normalizers), array($encoders));

        $jsonContent = $serializer->serialize($product, 'json');

        return $jsonContent;

    }

    //get products for the given user API
    //curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/products
    public function getProductsAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $product = $this
            ->getDoctrine()
            ->getRepository('VitchkovskiProductsBundle:Product')
            ->findBy(array('user' => $user->getUserId()));

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $encoders = new JsonEncoder();
        $normalizers = new ObjectNormalizer();

        //handling circular reference
        $normalizers->setCircularReferenceHandler(function ($category) {
            return $category->getCategories();
        });

        $normalizers->setIgnoredAttributes(array('user', 'categories1'));


        $serializer = new Serializer(array($normalizers), array($encoders));

        $jsonContent = $serializer->serialize($product, 'json');

        return $jsonContent;
    }

    //creating new product API
    //curl -v -H "Accept: application/json" -H "Content-type: application/json" -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" POST -d "{\"product\": {\"product_name\": \"foo\"}}" http://vitchkovski.com/api/products/new
    public function getProductsNewAction(Request $request)
    {
        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //creating new product entity
        $product = new Product();

        //preparing form
        $form = $this->createForm('Vitchkovski\ProductsBundle\Form\ProductType', $product);
        $form->handleRequest($request);


        if ($form->isValid()) {
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
            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();

            $encoders = new JsonEncoder();
            $normalizers = new ObjectNormalizer();

            //handling circular reference
            $normalizers->setCircularReferenceHandler(function ($category) {
                return $category->getCategories();
            });

            $normalizers->setIgnoredAttributes(array('user', 'categories1'));


            $serializer = new Serializer(array($normalizers), array($encoders));

            $jsonContent = $serializer->serialize($product, 'json');

            return new View($jsonContent, Response::HTTP_CREATED);
        }

        return View::create($form, 400);

    }

    //product delete API
    //curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/products/11/remove
    public function getProductsRemoveAction($id)
    {
        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //searching for the product ot delete. Should belong to the logged user.
        $product = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:Product')
            ->findOneBy(array('user' => $user->getUserId(), 'product_id' => $id));

        if (!$product) {
            //There is no such product.
            throw $this->createNotFoundException('Product not found');
        }

        //deleting user product
        $this->getDoctrine()->getManager()
            ->remove($product);

        $this->getDoctrine()->getManager()
            ->flush();

    }

    //edit Product API
    //curl -v -H "Accept: application/json" -H "Content-type: application/json" -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" PUT -d "{\"product\":{\"product_name\":\"QWERTY3\",\"categories\":[{\"category_name\":\"\"},{\"category_name\":\"Category3\"}]}}" http://vitchkovski.com/api/products/16/edit
    public function getProductsEditAction($id, Request $request)
    {
        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //searching for the product to edit. Should belong to the logged user.
        $product = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:Product')
            ->getProductWithCategories($id, $user->getUserId());

        if (!$product) {
            //There is no such product.
            throw $this->createNotFoundException('Product not found');
        }

        //Retrieving product's categories
        $categories = $product->getCategories();

        //saving original product image name (before submit).
        $productImg = $product->getProductImgName();

        //rendering form
        $form = $this->createForm('Vitchkovski\ProductsBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isValid()) {

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
            $this->getDoctrine()->getManager()
                ->flush();

            $encoders = new JsonEncoder();
            $normalizers = new ObjectNormalizer();

            //handling circular reference
            $normalizers->setCircularReferenceHandler(function ($category) {
                return $category->getCategories();
            });

            $normalizers->setIgnoredAttributes(array('user'));


            $serializer = new Serializer(array($normalizers), array($encoders));

            $jsonContent = $serializer->serialize($product, 'json');

            return new View($jsonContent, Response::HTTP_CREATED);
        }

        return View::create($form, 400);
    }



}