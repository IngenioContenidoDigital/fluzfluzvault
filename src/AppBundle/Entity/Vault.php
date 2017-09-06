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
    public $id_vault;
    
    /** @ORM\Column(type="string", length=255, unique=true)*/
    public $code;
    
    /** @ORM\Column(type="decimal", length=20)*/
    public $code_value;
    
    /** @ORM\Column(type="datetime")*/
    public $expiration;
    
    /** @ORM\Column(type="integer", nullable=true)
     *  @ORM\OneToOne(targetEntity="Member")
     *  @ORM\JoinColumn(name="id_member", referencedColumnName="id_member")
     */
    public $id_member;
    
    public function getIdVault(){
        return $this->id_vault;
    }
    public function getCode(){
        return $this->code;
    }
    public function getCodeValue(){
        return $this->code_value;
    }
    public function getExpiration(){
        return $this->expiration;
    }
    
    public function setCode($code){
        $this->code=$code;
    }
    public function setCodeValue($code_value){
        $this->code_value=$code_value;
    }
    public function setExpiration($expiration){
        $this->expiration=$expiration;
    }
    
    public function AssignCode($id_member){
        $this->id_member=$id_member;
    }
}
