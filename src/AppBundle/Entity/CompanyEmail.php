<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="company_email")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CompanyEmailRepository")
 */
class CompanyEmail{
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO") 
     */
    private $id;

    
    /** @ORM\Column(type="string", length=255, unique=true)*/
    private $name;
    
    /** @ORM\Column(type="text")*/
    private $template;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company", inversedBy="template")
     * @ORM\JoinColumn(name="company_id",referencedColumnName="id")
     */
    private $company;
    
    public function __construct(){
        $this->company = new ArrayCollection();
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
     * @return CompanyEmail
     */
    public function setCompany(Company $company){
        $this->company = $company;
        return $this;
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
     * Set Name
     *
     * @param string $name
     *
     * @return CompanyEmail
     */
    public function setName($name){
        $this->name = $name;
        return $this;
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName(){
        return $this->name;
    }
    
    /**
     * Set template
     *
     * @param text $template
     *
     * @return CompanyEmail
     */
    public function setTemplate($template){
        $this->template = $template;
        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate(){
        return $this->template;
    }
}

