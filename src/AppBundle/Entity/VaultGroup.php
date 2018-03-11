<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="vault_group")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VaultGroupRepository")
 */
class VaultGroup{
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Vault", mappedBy="group") 
     */
    private $vault;
    
    public function __construct(){
        $this->vault = new ArrayCollection();
    }
    
    /**
     * @return Collection|Vault[]
     */
    public function getVault(){
        return $this->vault;
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
     * Get Name of Vault Group
     * @return string
     */
    public function getName(){
        return $this->name;
    }
    
    /**
     * @param string $name
     * @return VaultGroup
     */
    public function setName($name){
        $this->name=$name;
        return $this;
    }
}

