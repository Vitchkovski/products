<?php

namespace Vitchkovski\ProductsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="products_x_categories")
 * @ORM\HasLifecycleCallbacks
 */
class ProductCategory
{
    /**
     * @ORM\id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $sort_id;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="products_x_categories")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id")
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products_x_categories")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id")
     */
    protected $category;

    /**
     * Set sort_id
     *
     * @param integer $sortId
     * @return ProductCategory
     */
    public function setSortId($sortId)
    {
        $this->sort_id = $sortId;

        return $this;
    }

    /**
     * Get sort_id
     *
     * @return integer 
     */
    public function getSortId()
    {
        return $this->sort_id;
    }

    /**
     * Set product_id
     *
     * @param \Vitchkovski\ProductsBundle\Entity\Product $productId
     * @return ProductCategory
     */
    public function setProductId(\Vitchkovski\ProductsBundle\Entity\Product $productId = null)
    {
        $this->product_id = $productId;

        return $this;
    }

    /**
     * Get product_id
     *
     * @return \Vitchkovski\ProductsBundle\Entity\Product
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * Set category_id
     *
     * @param \Vitchkovski\ProductsBundle\Entity\Category $categoryId
     * @return ProductCategory
     */
    public function setCategoryId(\Vitchkovski\ProductsBundle\Entity\Category $categoryId = null)
    {
        $this->category_id = $categoryId;

        return $this;
    }

    /**
     * Get category_id
     *
     * @return \Vitchkovski\ProductsBundle\Entity\Category
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Set product
     *
     * @param \Vitchkovski\ProductsBundle\Entity\Product $product
     * @return ProductCategory
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

    /**
     * Set category
     *
     * @param \Vitchkovski\ProductsBundle\Entity\Category $category
     * @return ProductCategory
     */
    public function setCategory(\Vitchkovski\ProductsBundle\Entity\Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \Vitchkovski\ProductsBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
