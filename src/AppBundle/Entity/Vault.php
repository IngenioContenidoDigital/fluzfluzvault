<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


/**
 * @ORM\Table(name="vault")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VaultRepository")
 * 
 */
class Vault {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO") 
     */
    public $id;
    
    /** @ORM\Column(type="string", length=255, unique=true)*/
    public $code;
    
    /** @ORM\Column(type="decimal", length=20)*/
    public $code_value;
    
    /** @ORM\Column(type="datetime")*/
    public $expiration;
    
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    public $assigned;
    
    /**
     * ORM\OneToOne(targetEntity="AppBundle\Entity\Member", inversedBy="vault")
     * @ORM\JoinColumn(name="member_id",referencedColumnName="id")
     */
    private $members;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\VaultGroup", inversedBy="vault")
     * @ORM\JoinColumn(name="vault_group_id",referencedColumnName="id")
     */
    private $group;
    
    
    /**
     * @return Collection|VaultGroup[]
     */
    public function getgroup(){
        return $this->group;
    }
    
    public function setGroup(VaultGroup $group){
        $this->group=$group;
        return $this;
    }
    
    /**
     * @return Collection|Member[]
     */
    public function getMember(){
        return $this->members;
    }

    public function setMember(Member $member){
        $this->members = $member;
        return $this;
    }
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company", inversedBy="codes")
     * @ORM\JoinColumn(name="company_id",referencedColumnName="id")
     */
    private $company;
    
    /**
     * @return Collection|Company[]
     */
    public function getCompany(){
        return $this->company;
    }

    public function setCompany(Company $company){
        $this->company = $company;
        return $this;
    }
    
    public function __construct(){
        $this->members = new ArrayCollection();
        $this->company = new ArrayCollection();
        $this->group = new ArrayCollection();
    }
    
    /**
     * @return integer
     */
    public function getIdVault(){
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getCode(){
        return $this->code;
    }
    
    /**
     * @return decimal
     */
    public function getCodeValue(){
        return $this->code_value;
    }
    
    /**
     * @return datetime
     */
    public function getExpiration(){
        return $this->expiration;
    }
    
    /**
     * @return datetime
     */
    public function getAssigned(){
        return $this->assigned;
    }
    
    /**
     * @param string $code
     * @return Vault
     */
    public function setCode($code){
        $this->code=$code;
        return $this;
    }
    /**
     * @param decimal $code_value
     * @return Vault
     */
    public function setCodeValue($code_value){
        $this->code_value=$code_value;
        return $this;
    }
    
    /**
     * @param datetime $expiration
     * @return Vault
     */
    public function setExpiration(\DateTime $expiration){
        $this->expiration=$expiration;
        return $this;
    }
    
    /**
     * @param datetime $assigned
     * @return Vault
     */
    public function setAssigned(\DateTime $assigned){
        $this->assigned=$assigned;
        return $this;
    }
    
    /*public function AssignCode($id_member){
        $this->id_member=$id_member;
    }*/
}
