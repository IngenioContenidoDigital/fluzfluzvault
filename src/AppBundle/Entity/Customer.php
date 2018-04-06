<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
    public $id_customer;
    
    /** @ORM\Column(type="string", length=255) */
    public $name;
    
    /** @ORM\Column(type="string", length=255)*/
    public $password;
      
    /** @ORM\Column(type="string", length=100, unique=true)*/
    public $email;    
    
    /** @ORM\Column(name="is_active", type="boolean")*/
    private $isActive;  
    
    /** @ORM\Column(type="string", length=255)*/
    public $role;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company", inversedBy="users")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;
    
    /**
     * @return Collection|Company[]
     */
    public function getCompany(){
        return $this->company;
    }

    public function setMember(Member $member){
        $this->member = $member;
    }
    
    public function __construct(){
        $this->isActive = true;
        $this->company = new ArrayCollection();
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
       return explode(',',$this->role);
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
     * Set Role
     * 
     * @param string $role
     * 
     * @return Customer
     */
    public function setRoles($role){
        if($this->role == NULL){
            $this->role=$role;
        }else{
            $lista_roles=explode(',',$this->role);
            if(!in_array($role,$lista_roles)){
                array_push($lista_roles,$role);
                $this->role=  implode(',', $lista_roles);
            }
        }
        return $this;
    }
    
    /**
     * Remove Role
     * 
     * @param string $role
     * 
     * @return Customer
     */
    public function removeRole($role){
        $lista_roles=explode(',',$this->role);
        if(in_array($role,$lista_roles)){ 
            $index = array_search($role, $lista_roles);
            if($index !== false){
                unset($lista_roles[$index]);
            }

            $this->role=  implode(',', $lista_roles);
        }
        return $this;
    }

    /**
     * Set company
     *
     * @param Company $company
     *
     * @return Customer
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;

        return $this;
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
        return $this->id_customer;
    }
}
