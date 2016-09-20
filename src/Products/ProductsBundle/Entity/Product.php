<?php

namespace Products\ProductsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="user_products")
 * @ORM\HasLifecycleCallbacks
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $product_id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="products")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $product_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {"image/jpeg", "image/gif", "image/png"},
     *     maxSizeMessage = "The maxmimum allowed file size is 5MB.",
     *     mimeTypesMessage = "Only the filetypes image are allowed."
     * )
     */
    protected $product_img_name;

    /**
     * @ORM\OneToMany(targetEntity="ProductCategory", mappedBy="product", cascade={"all"}, orphanRemoval=true)
     */
    protected $products_x_categories;

    protected $categories;

    /**
     * Get product_id
     *
     * @return integer 
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * Set product_name
     *
     * @param string $productName
     * @return Product
     */
    public function setProductName($productName)
    {
        $this->product_name = $productName;

        return $this;
    }

    /**
     * Get product_name
     *
     * @return string 
     */
    public function getProductName()
    {
        return $this->product_name;
    }

    /**
     * Set product_img_name
     *
     * @param string $productImgName
     * @return Product
     */
    public function setProductImgName($productImgName)
    {
        $this->product_img_name = $productImgName;

        return $this;
    }

    /**
     * Get product_img_name
     *
     * @return string 
     */
    public function getProductImgName()
    {
        return $this->product_img_name;
    }

    /**
     * Set user
     *
     * @param \Products\ProductsBundle\Entity\User $user
     * @return Product
     */
    public function setUser(\Products\ProductsBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Products\ProductsBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products_x_categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add products_x_categories
     *
     * @param \Products\ProductsBundle\Entity\ProductCategory $productsXCategories
     * @return Product
     */
    public function addProductsXCategory(\Products\ProductsBundle\Entity\ProductCategory $productsXCategories)
    {
        $this->products_x_categories[] = $productsXCategories;

        return $this;
    }

    /**
     * Remove products_x_categories
     *
     * @param \Products\ProductsBundle\Entity\ProductCategory $productsXCategories
     */
    public function removeProductsXCategory(\Products\ProductsBundle\Entity\ProductCategory $productsXCategories)
    {
        $this->products_x_categories->removeElement($productsXCategories);
    }

    /**
     * Get products_x_categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductsXCategories()
    {
        return $this->products_x_categories;
    }

    public function addCategory(Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    public function removeCategory(Category $category)
    {
        $this->categories->removeElement($category);
    }


    public function getCategories()
    {
        return $this->categories;
    }
}
