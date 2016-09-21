<?php

namespace Vitchkovski\ProductsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;


/**
 * @ORM\Entity(repositoryClass="Vitchkovski\ProductsBundle\Repository\UserRepository")
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields="email", message="Sorry, this email address is already in use.", groups={"registration"})
 * @UniqueEntity(fields="username", message="Username is already taken", groups={"registration"})
 */
class User implements UserInterface
{

    public function __construct()
    {
        $this->products = new ArrayCollection();

    }

    /**
     * @ORM\id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $user_id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "Your name must be at least {{ limit }} characters long",
     *      maxMessage = "Your name cannot be longer than {{ limit }} characters", groups={"registration"}
     * )
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(groups={"registration"})
     * @Assert\Email(groups={"registration"})
     * @Assert\Length(
     *      min = 5,
     *      max = 50,
     *      minMessage = "Your email must be at least {{ limit }} characters long",
     *      maxMessage = "Your email cannot be longer than {{ limit }} characters", groups={"registration"}
     * )
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 6,
     *      max = 100,
     *      minMessage = "Your password must be at least {{ limit }} characters long",
     *      maxMessage = "Your password cannot be longer than {{ limit }} characters", groups={"registration"}
     * )
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $hash_key;


    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="user")
     */
    protected $products;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }


    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function eraseCredentials()
    {
        return;
    }

    public function getSalt()
    {

    }



    /**
     * Add products
     *
     * @param \Vitchkovski\ProductsBundle\Entity\Product $products
     * @return User
     */
    public function addProduct(Product $products)
    {
        $this->products[] = $products;

        return $this;
    }

    /**
     * Remove products
     *
     * @param \Vitchkovski\ProductsBundle\Entity\Product $products
     */
    public function removeProduct(Product $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set hash_key
     *
     * @param string $hashKey
     * @return User
     */
    public function setHashKey($hashKey)
    {
        $this->hash_key = $hashKey;

        return $this;
    }

    /**
     * Get hash_key
     *
     * @return string 
     */
    public function getHashKey()
    {
        return $this->hash_key;
    }

}
