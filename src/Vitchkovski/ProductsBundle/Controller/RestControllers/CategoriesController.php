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


class CategoriesController extends FOSRestController
{

    public function getCategoryAction($id)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $category = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:Category')
            ->getCategoryByUser($id, $user->getUserId());

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $encoders = new JsonEncoder();
        $normalizers = new ObjectNormalizer();

        $normalizers->setCircularReferenceHandler(function ($product) {
            return $product->getProduct();
        });

        $normalizers->setIgnoredAttributes(array('user'));


        $serializer = new Serializer(array($normalizers), array($encoders));

        $jsonContent = $serializer->serialize($category, 'json');

        return $jsonContent;
    }

    public function getCategoryRemoveAction($id)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $category = $this->getDoctrine()->getManager()
            ->getRepository('VitchkovskiProductsBundle:Category')
            ->getCategoryByUser($id, $user->getUserId());

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        //deleting user product
        $this->getDoctrine()->getManager()
            ->remove($category);

        $this->getDoctrine()->getManager()
            ->flush();

    }
}