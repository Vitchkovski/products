<?php

namespace Vitchkovski\ProductsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="Vitchkovski\ProductsBundle\Repository\ProductRepository")
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
     * @ORM\OneToMany(targetEntity="Category", mappedBy="product", cascade={"persist", "remove"})
     */
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
     * @param \Vitchkovski\ProductsBundle\Entity\User $user
     * @return Product
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
     * Constructor
     */
    public function __construct()
    {
        $this->categories = new ArrayCollection();
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
