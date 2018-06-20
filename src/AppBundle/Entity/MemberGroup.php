<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="member_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MemberGroupRepository")
 */
class MemberGroup{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO") 
     */
    private $id;
    
    /**
     *@ORM\Column(type="string", length=255, unique=true)
     */
    public $name;
    
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Member", mappedBy="group") 
     * @ORM\JoinTable(name="groups_members")
     */
    private $members;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company", inversedBy="groups")
     * @ORM\JoinColumn(name="company_id",referencedColumnName="id")
     */
    private $company;
    
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
     * @return MemberGroup
     */
    public function setCompany(Company $company){
        $this->company = $company;
        return $this;
    }
    
    /**
     * @return Collection|Member[]
     */
    public function getMembers(){
        return $this->members;
    }
    
    public function addMember(Member $member){
        $member->setGroup($this); // synchronously updating inverse side
        $this->members[] = $member;
    }

    public function __construct(){
        $this->members = new ArrayCollection();
    }
    /**
     * Get id
     *
     * @return int
     */
    public function getId(){
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getName(){
        return $this->name;
    }
    
    /**
     * @param string $name
     * @return MemberGroup
     */
    public function setName($name){
        $this->name=$name;
        return $this;
    }
    
    public function hasMember(Member $member){
        return $this->members->contains($member);
    }
    
    public function findMemberByEmail($param){
        foreach($this->members as $m){
            if (($m->getMemberEmail()== $param)){
                return true;
            }
        }
        return false;
    }
    
    public function findMemberByIdentification($param){
        foreach($this->members as $m){
            if ($m->getIdentification()== $param){
                return true;
            }
        }
        return false;
    }
}

