<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="members")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MemberRepository")
 */

class Member {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO") 
     */
    public $id;
    
    /** @ORM\Column(type="string", length=255)*/
    public $member_name;
    
    /** @ORM\Column(type="string", length=50, unique=true)*/
    public $member_email;
    
    /** @ORM\Column(type="decimal", length=10)*/
    public $mobile_phone;
    
    /** @ORM\Column(type="string", length=12, unique=true)*/
    public $identification;
    
    /**
     * @Assert\DateTime() 
     * @ORM\Column(type="datetime")
     */
    public $date_add;
    
    /** @ORM\Column(type="string", length=255, nullable=true)*/
    public $optional_1;
    
    /** @ORM\Column(type="string", length=255, nullable=true)*/
    public $optional_2;
    
    /** @ORM\Column(type="string", length=255, nullable=true)*/
    public $optional_3;
    
    /** @ORM\Column(type="string", length=255, nullable=true)*/
    public $optional_4;
    
    /** @ORM\Column(type="string", length=255, nullable=true)*/
    public $optional_5;
    
      
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\MemberGroup", inversedBy="members")
     * ORM\JoinColumn(name="member_group_id",referencedColumnName="id")
     */
    private $group;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Vault", mappedBy="members")
     */
    private $vault;
    
    public function getVault(){
        return $this->vault;
    }
    
    /**
     * @return Collection|Vault[]
     */
    public function setVault(Vault $vault){
        $this->vault = $vault;
    }
    
    
    
    /**
     * @return Collection|MemberGroup[]
     */
    public function getGroup(){
        return $this->group;
    }
    
    /**
     * Set group
     *
     * @param MemberGroup $group
     *
     * @return Member
     */
    public function setGroup(MemberGroup $group){
        $this->group[] = $group;
        return $this;
    }
    
    /**
     * Get idMember
     *
     * @return integer
     */
    public function getIdMember()
    {
        return $this->id;
    }
    
    /**
     * Get member_name
     *
     * @return string
     */
    public function getMemberName(){
        return $this->member_name;
    }
    /**
     * Get member_email
     *
     * @return string
     */
    public function getMemberEmail(){
        return $this->member_email;
    }
    /**
     * Get mobile_phone
     *
     * @return int
     */
    public function getMobilePhone(){
        return $this->mobile_phone;
    }
    /**
     * Get identification
     *
     * @return string
     */
    public function getIdentification(){
        return $this->identification;
    }
    /**
     * Get date_add
     *
     * @return datetime
     */
    public function getDateAdd(){
        return $this->date_add;
    }
    
    public function dateCreated(){
        return date_format($this->date_add,'%Y-%m-%d');
    }
    
    /**
     * Set member_name
     *
     * @param string $member_name
     *
     * @return Member
     */
    public function setMemberName($member_name){
        $this->member_name=$member_name;
        return $this;
    }
    /**
     * Set member_email
     *
     * @param string $member_email
     *
     * @return Member
     */
    public function setMemberEmail($member_email){
        $this->member_email=$member_email;
        return $this;
    }
    /**
     * Set mobile_phone
     *
     * @param string $mobile_phone
     *
     * @return Member
     */
    public function setMobilePhone($mobile_phone){
        $this->mobile_phone=$mobile_phone;
        return $this;
    }
    /**
     * Set identification
     *
     * @param string $identification
     *
     * @return Member
     */
    public function setIdentification($identification){
        $this->identification=$identification;
        return $this;
    }
    /**
     * Set date_add
     *
     * @param datetime $date_Add
     *
     * @return Member
     */
    public function setDateAdd($date_add){
        $this->date_add = new \DateTime("now");
        //$this->date_add=$date_add;
        return $this;
    }
    
    public function __construct(){
        //$this->company = new ArrayCollection();
        $this->group = new ArrayCollection();
        $this->vault = new ArrayCollection();
        
    }
    
    /**
     * Set optional 1
     *
     * @param string $optional_1
     *
     * @return Member
     */
    public function setOptional1($optional_1){
        $this->optional_1=$optional_1;
        return $this;
    }
    /**
     * Set optional 2
     *
     * @param string $optional_2
     *
     * @return Member
     */
    public function setOptional2($optional_2){
        $this->optional_2=$optional_2;
        return $this;
    }
    /**
     * Set optional 3
     *
     * @param string $optional_3
     *
     * @return Member
     */
    public function setOptional3($optional_3){
        $this->optional_3=$optional_3;
        return $this;
    }
    /**
     * Set optional 4
     *
     * @param string $optional_4
     *
     * @return Member
     */
    public function setOptional4($optional_4){
        $this->optional_4=$optional_4;
        return $this;
    }
    /**
     * Set optional 5
     *
     * @param string $optional_5
     *
     * @return Member
     */
    public function setOptional5($optional_5){
        $this->optional_5=$optional_5;
        return $this;
    }
    
    /**
     * Get optional 1
     *
     * @return string
     */
    public function getOptional1(){
        return $this->optional_1;
    }
    /**
     * Get optional 2
     *
     * @return string
     */
    public function getOptional2(){
        return $this->optional_2;
    }
    /**
     * Get optional 3
     *
     * @return string
     */
    public function getOptional3(){
        return $this->optional_3;
    }
    /**
     * Get optional 4
     *
     * @return string
     */
    public function getOptional4(){
        return $this->optional_4;
    }
    /**
     * Get optional 5
     *
     * @return string
     */
    public function getOptional5(){
        return $this->optional_5;
    }
}
