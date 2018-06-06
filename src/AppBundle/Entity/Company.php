<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="company")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CompanyRepository")
 */
class Company
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO") 
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    public $name;
    /**
     * @ORM\Column(type="string", length=15, unique=true)
     */
    public $nit;
    /**
     * @ORM\Column(type="string", length=100)
     */
    public $email;
    /**
     * @ORM\Column(type="string", length=255)
     */
    public $logo;
    /**
     * @ORM\Column(type="decimal", length=15)
     */
    public $phone;
    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Member", inversedBy="company") 
     * @ORM\JoinTable(name="company_members")
     */
    private $members;
    /**
     * @return Collection|Member[]
     */
    public function getMembers(){
        return $this->members;
    }
    
    
    public function addMember(Member $member){
        $member->setCompany($this); // synchronously updating inverse side
        $this->members[] = $member;
    }
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Vault", mappedBy="company")
     */
    private $codes;
    
    /**
     * @return Collection|Vault[]
     */
    public function getCodes(){
        return $this->codes;
    }
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Customer", mappedBy="company", cascade={"persist"})
     */
    private $users;
    
    /**
     * @return Collection|Customer[]
     */
    public function getUsers(){
        return $this->users;
    }
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CompanyEmail", mappedBy="company") 
     */
    private $template;
    
    public function __construct(){
        $this->members = new ArrayCollection();
        $this->codes = new ArrayCollection();
        $this->users= new ArrayCollection();
        $this->template = new ArrayCollection();
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
     * @return string
     */
    public function getNit(){
        return $this->nit;
    }
    
    /**
     * @return string
     */
    public function getEmail(){
        return $this->email;
    }
    
    /**
     * @return string
     */
    public function getLogo(){
        return $this->logo;
    }
    
    /**
     * @return int
     */
    public function getPhone(){
        return $this->phone;
    }
    
    /**
     * @param string $company_name
     *
     * @return Company
     */
    public function setName($company_name){
        $this->name=$company_name;
        return $this;
    }
    /**
     * @param string $company_email
     *
     * @return Company
     */
    public function setEmail($company_email){
        $this->email=$company_email;
        return $this;
    }
    /**
     * @param string $company_nit
     *
     * @return Company
     */
    public function setNit($company_nit){
        $this->nit=$company_nit;
        return $this;
    }
    /**
     * @param string $company_logo
     *
     * @return Company
     */
    public function setLogo($company_logo){
        $this->logo=$company_logo;
        return $this;
    }
    /**
     * @param int $company_phone
     *
     * @return Company
     */
    public function setPhone($company_phone){
        $this->phone=$company_phone;
        return $this;
    }
    
    /**
     * @return Collection|CompanyEmail[]
     */
    public function getTemplates(){
        return $this->template;
    }
}

