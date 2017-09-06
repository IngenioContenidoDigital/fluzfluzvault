<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
    public $id_member;
    
    /** @ORM\Column(type="string", length=255)*/
    public $member_name;
    
    /** @ORM\Column(type="string", length=50, unique=true)*/
    public $member_email;
    
    /** @ORM\Column(type="decimal", length=10)*/
    public $mobile_phone;
    
    /** @ORM\Column(type="string", length=12, unique=true)*/
    public $identification;
    
    /** @ORM\Column(type="datetime")*/
    public $date_add;
    

    /**
     * Get idMember
     *
     * @return integer
     */
    public function getIdMember()
    {
        return $this->id_member;
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
        $this->date_add=$date_add;
        return $this;
    }
    
}
