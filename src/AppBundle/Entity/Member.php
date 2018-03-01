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
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company", inversedBy="members")
     * @ORM\JoinColumn(name="company_id",referencedColumnName="id")
     */
    private $company;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MemberGroup", inversedBy="members")
     * @ORM\JoinColumn(name="member_group_id",referencedColumnName="id")
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
     * @return Collection|Company[]
     */
    public function getCompany(){
        return $this->company;
    }
    
    /**
     * Set company
     *
     * @param Company $company
     *
     * @return Member
     */
    public function setCompany(Company $company){
        $this->company = $company;
        return $this;
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
        $this->group = $group;
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
        $this->company = new ArrayCollection();
        $this->group = new ArrayCollection();
        $this->vault = new ArrayCollection();
        
    }
    
}
