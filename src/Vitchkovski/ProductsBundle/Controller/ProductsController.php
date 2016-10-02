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

        $serializer = $this->get('serializer');
        dump($products);

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

        dump($request);

        //if form was submitted a new product must be created in the DB
        if ($form->isSubmitted() && $form->isValid()) {

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
            $this->getDoctrine()->getManager()->persist($product);
            $this->getDoctrine()->getManager()->flush();

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
