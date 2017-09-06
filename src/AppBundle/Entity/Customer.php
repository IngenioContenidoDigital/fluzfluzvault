<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerRepository")
 * @ORM\Table(name="customer")
 */

class Customer implements AdvancedUserInterface, \Serializable{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO") 
     */
    private $id_customer;
    
    /** @ORM\Column(type="string", length=255) */
    public $name;
    
    /** @ORM\Column(type="string", length=255)*/
    public $password;
    
    /** @ORM\Column(type="string", length=255)*/
    public $company;
    
    /** @ORM\Column(type="string", length=100, unique=true)*/
    public $email;    
    
    /** @ORM\Column(name="is_active", type="boolean")*/
    private $isActive;  
    
    public function __construct(){
        $this->isActive = true;        
    }
    
    public function setName($name){
        $this->name=$name;
    }
    
    public function setEmail($email){
        $this->email=$email;
    }
    
    public function getName(){
        return $this->name;
    }
    
    public function getEmail(){
        return $this->email;
    }
    
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id_customer,
            $this->email,
            $this->password,
            $this->isActive,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id_customer,
            $this->email,
            $this->password,
            $this->isActive,
        ) = unserialize($serialized);
    }
    
    public function getRoles(){
       return array('ROLE_USER');
    }
    public function getPassword(){
        return $this->password;
    }
    public function getSalt(){
        return null;
    }
    public function getUsername(){
        return $this->email;
    }
    public function eraseCredentials(){
        return null;
    }
    
    public function isAccountNonExpired(){
        return true;
    }

    public function isAccountNonLocked(){
        return true;
    }

    public function isCredentialsNonExpired(){
        return true;
    }

    public function isEnabled(){
        return $this->isActive;
    }
    /**
     * @var integer
     */
    private $idCustomer;


    /**
     * Set password
     *
     * @param string $password
     *
     * @return Customer
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set company
     *
     * @param string $company
     *
     * @return Customer
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return Customer
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Get idCustomer
     *
     * @return integer
     */
    public function getIdCustomer()
    {
        return $this->idCustomer;
    }
}
