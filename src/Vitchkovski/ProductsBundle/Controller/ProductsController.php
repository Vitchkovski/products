<?php

namespace Vitchkovski\ProductsBundle\Controller;

use Vitchkovski\ProductsBundle\Entity\Category;
use Vitchkovski\ProductsBundle\Entity\Product;
use Vitchkovski\ProductsBundle\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class ProductsController extends Controller
{
    public function indexAction()
    {
        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //retrieving user products
        $products = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:Product')
            ->findBy(array('user' => $user->getUserId()), array('product_id' => 'DESC'));

        return $this->render('VitchkovskiProductsBundle:Products:userPersonalPage.html.twig', array(
            'products' => $products
        ));
    }

    public function createAction(Request $request)
    {

        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //creating new product entity
        $product = new Product();

        //preparing form
        $form = $this->createForm('Vitchkovski\ProductsBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        //if form was submitted a new product must be created in the DB
        if ($form->isSubmitted() && $form->isValid()) {

            //general process of saving product to the DB
            $this->get('app.products_service')->saveCreateProductToDB($form, $user);

            //return to the products page
            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
        }

        return $this->render('VitchkovskiProductsBundle:Products:addProduct.html.twig', array(
            'product' => $product,
            'form' => $form->createView()
        ));
    }

    public function deleteAction($product_id)
    {

        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //searching for the product ot delete. Should belong to the logged user.
        $product = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:Product')
            ->findOneBy(array('user' => $user->getUserId(), 'product_id' => $product_id));

        if (!$product) {
            //There is no such product.
            $this->addFlash('notice', 'Product id is incorrect.');

            //stop executing, return to the personal page
            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');

        }

        //deleting user product
        $this->getDoctrine()->getManager()
            ->remove($product);

        $this->getDoctrine()->getManager()
            ->flush();

        $this->addFlash('notice', "Product has been deleted successfully!");

        //returning to the personal page
        return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
    }

    public function editAction($product_id, Request $request)
    {

        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //searching for the product to edit. Should belong to the logged user.
        $product = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:Product')
            ->getProductWithCategories($product_id, $user->getUserId());

        if (!$product) {
            //There is no such product.
            $this->addFlash('notice', 'Product id is incorrect.');

            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
        }

        //Retrieving product's categories
        $categories = $product->getCategories();

        //saving original product image name (before submit).
        $productImg = $product->getProductImgName();

        //rendering form
        $form = $this->createForm('Vitchkovski\ProductsBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //general process  for ssaving edited product to the DB
            $this->get('app.products_service')->saveEditProductToDB($form, $user, $productImg);

            //return to the personal page
            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
        }

        //form was not submitted yet, rendering form
        return $this->render('VitchkovskiProductsBundle:Products:editProduct.html.twig', array(
            'form' => $form->createView(),
            'product' => $product,
            'categories_number' => count($categories),
            'product_img' => $product->getProductImgName(),
        ));
    }

}
