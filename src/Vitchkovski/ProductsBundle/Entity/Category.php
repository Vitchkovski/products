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
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="categories")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $category_id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="categories")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    protected $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $category_name;

    /**
     * @ORM\OneToMany(targetEntity="ProductCategory", mappedBy="category", cascade={"all"}, orphanRemoval=true)
     */
    protected $products_x_categories;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->products_x_categories = new \Doctrine\Common\Collections\ArrayCollection();

    }

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
     * Set user
     *
     * @param \Vitchkovski\ProductsBundle\Entity\User $user
     * @return Category
     */
    public function setUser(\Vitchkovski\ProductsBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Vitchkovski\ProductsBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add products_x_categories
     *
     * @param \Vitchkovski\ProductsBundle\Entity\ProductCategory $productsXCategories
     * @return Category
     */
    public function addProductsXCategory(\Vitchkovski\ProductsBundle\Entity\ProductCategory $productsXCategories)
    {
        $this->products_x_categories[] = $productsXCategories;

        return $this;
    }

    /**
     * Remove products_x_categories
     *
     * @param \Vitchkovski\ProductsBundle\Entity\ProductCategory $productsXCategories
     */
    public function removeProductsXCategory(\Vitchkovski\ProductsBundle\Entity\ProductCategory $productsXCategories)
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
}
