<?php

namespace Vitchkovski\ProductsBundle\Controller\RestControllers;

use FOS\RestBundle\Controller\FOSRestController;
use Vitchkovski\ProductsBundle\Entity\Product;
use Vitchkovski\ProductsBundle\Form\ProductType;
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

            //general process of saving product to the DB
            $product = $this->get('app.products_service')->saveCreateProductToDB($form, $user);

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
        //$categories = $product->getCategories();

        //saving original product image name (before submit).
        $productImg = $product->getProductImgName();

        //rendering form
        $form = $this->createForm('Vitchkovski\ProductsBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isValid()) {

            //general process  for ssaving edited product to the DB
            $this->get('app.products_service')->saveEditProductToDB($form, $user, $productImg);

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