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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Member", mappedBy="group", cascade={"remove"}) 
     */
    private $members;
    
    /**
     * @return Collection|Member[]
     */
    public function getMembers(){
        return $this->members;
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
}

