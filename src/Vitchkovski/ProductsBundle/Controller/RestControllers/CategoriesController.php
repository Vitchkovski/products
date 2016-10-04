<?php

namespace Vitchkovski\ProductsBundle\Controller\RestControllers;

use FOS\RestBundle\Controller\FOSRestController;
use Vitchkovski\ProductsBundle\Entity\Category;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use FOS\RestBundle\Controller\Annotations as Rest;


class CategoriesController extends FOSRestController
{

    //get category info API
    //curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/categories/7
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

        //circular reference must ne handled
        $normalizers->setCircularReferenceHandler(function ($product) {
            return $product->getProduct();
        });

        $normalizers->setIgnoredAttributes(array('user'));


        $serializer = new Serializer(array($normalizers), array($encoders));

        $jsonContent = $serializer->serialize($category, 'json');

        return $jsonContent;
    }

    //remove Category API
    //curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/categories/7/remove
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