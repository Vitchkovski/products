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
        $em = $this->getDoctrine()->getManager();
        $products = $em->getRepository('VitchkovskiProductsBundle:Product')->findBy(array('user' => $user->getUserId()), array('product_id' => 'DESC'));

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

        $errors = $this->getErrorsAsArray($form);


        //if form was submitted a new product must be created in the DB
        if ($form->isSubmitted() && $form->isValid()) {

            //Checking if file was submitted
            $file = $product->getProductImgName();
            if ($file) {
                //starting general processing process for uploaded images.
                //Generating new image name, cropping image, moving both original and cropped images to the user's folder.
                $fileName = $this->get('app.image_uploader')->upload($file, $user->getUserId());
                //dump($fileName);

                $product->setProductImgName($fileName);
            }


            //setting user info for a new product
            $product->setUser($user);

            //saving product (without categories yet)
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            //Retrieving submitted categories
            $categories = $product->getCategories();


            foreach ($categories as $category) {
                if ($category->getCategoryName() !== null) {
                    //we must create records in both user_categories and product_x_categories tables
                    $category->setUser($user);
                    $em->persist($category);


                    $productCategory = new ProductCategory();

                    $productCategory->setProduct($product);
                    $productCategory->setCategory($category);
                    $em->persist($productCategory);

                    $em->flush();
                }
            }

            //return to the products page
            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
        }

        return $this->render('VitchkovskiProductsBundle:Products:addProduct.html.twig', array(
            'product' => $product,
            'form' => $form->createView(),
            'errors' => $errors
        ));
    }

    public function deleteAction($product_id)
    {

        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //searching for the product ot delete. Should belong to the logged user.
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository('VitchkovskiProductsBundle:Product')->findOneBy(array('user' => $user->getUserId(),
            'product_id' => $product_id));

        if (!$product) {
            //There is no such product.
            $this->addFlash(
                'notice',
                'Product id is incorrect.');

            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');

        }

        //Retrieving product's categories to delete
        $categories = $em->getRepository('VitchkovskiProductsBundle:Category')->getCategoriesForProduct($product_id);

        foreach ($categories as $category) {
            $em->remove($category);
        }

        //deleting user product
        $em->remove($product);

        $em->flush();

        $this->addFlash('notice', "Product has been deleted successfully!");


        //returning to the personal page
        return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
    }

    public function editAction($product_id, Request $request)
    {

        //retrieving user info
        $user = $this->get('security.token_storage')->getToken()->getUser();

        //searching for the product to edit. Should belong to the logged user.
        $em = $this->getDoctrine()->getManager();


        $product = $em->getRepository('VitchkovskiProductsBundle:Product')->findOneBy(array('user' => $user->getUserId(),
            'product_id' => $product_id));


        if (!$product) {
            //There is no such product.
            $this->addFlash(
                'notice',
                'Product id is incorrect.');

            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');

        }

        //Retrieving product's categories
        $categories = $em->getRepository('VitchkovskiProductsBundle:Category')->getCategoriesForProduct($product_id);

        foreach ($categories as $category) {
            $product->addCategory($category);
        }

        //saving product image name
        $productImg = $product->getProductImgName();

        //we don't need string image name when creating input form
        $product->setProductImgName(null);
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

                //saving initial image name (whether it is null or not)
                $product->setProductImgName($productImg);
            }

            //saving product (without categories yet)
            $em->persist($product);
            $em->flush();

            $categories = $product->getCategories();
            //dump($categories);

            foreach ($categories as $category) {

                //if category submitted is null it means we have to delete it
                if ($category->getCategoryName() == null) {

                    $em->remove($category);

                    $em->flush();

                } else {

                    //we must create records in both user_categories and product_x_categories tables
                    $category->setUser($user);

                    $em->remove($category);
                    $em->flush();


                    //connection must be created only if it is not exist already
                    if (count($category->getProductsXCategories()) == 0) {
                        $productCategory = new ProductCategory();
                        $productCategory->setProduct($product);
                        $productCategory->setCategory($category);

                        $category->addProductsXCategory($productCategory);
                    }

                    $em->persist($category);
                    $em->flush();


                }

            }


            return $this->redirectToRoute('VitchkovskiProductsBundle_userPersonalPage');
        }


        return $this->render('VitchkovskiProductsBundle:Products:editProduct.html.twig', array(
            'form' => $form->createView(),
            'product' => $product,
            'categories_number' => count($categories),
            'product_img' => $productImg,
        ));
    }

    public function getErrorsAsArray($form)
    {
        //getting form validation array as an array for further handling
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $key => $child) {
            if ($err = $this->getErrorsAsArray($child)) {
                $errors[$key] = $err;
            }
        }

        return $errors;
    }

}
