<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Member")
     * @ORM\JoinColumn(name="member_id",referencedColumnName="id")
     */
    private $member;
    
    public function getMember(){
        return $this->member;
    }

    public function setMember(Member $member){
        $this->member = $member;
    }
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company", inversedBy="codes")
     * @ORM\JoinColumn(name="company_id",referencedColumnName="id")
     */
    private $company;
    
    public function getCompany(){
        return $this->company;
    }

    public function setCompany(Company $company){
        $this->company = $company;
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
