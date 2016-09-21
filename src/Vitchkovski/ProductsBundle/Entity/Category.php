<?php

namespace Vitchkovski\ProductsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="Vitchkovski\ProductsBundle\Repository\CategoryRepository")
 * @ORM\Table(name="user_categories")
 * @ORM\HasLifecycleCallbacks
 */
class Category
{

    /**
     * @ORM\id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $category_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $category_name;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="categories")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id")
     */
    protected $product;

    /**
     * Get category_id
     *
     * @return integer 
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Set category_name
     *
     * @param string $categoryName
     * @return Category
     */
    public function setCategoryName($categoryName)
    {
        $this->category_name = $categoryName;

        return $this;
    }

    /**
     * Get category_name
     *
     * @return string 
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }


    /**
     * Set product
     *
     * @param \Vitchkovski\ProductsBundle\Entity\Product $product
     * @return Category
     */
    public function setProduct(\Vitchkovski\ProductsBundle\Entity\Product $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Vitchkovski\ProductsBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }
}
