<?php

// src/AppBundle/Repository/CustomerRepository.php
namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;


class VaultRepository extends EntityRepository{
    public function findCodeValues(){
        return $this->getEntityManager()
                ->createQuery('SELECT DISTINCT v.code_value FROM AppBundle:Vault v WHERE v.id_member is NULL')
                ->getResult();
    }
    
    public function findByCode($code){
        return $this->getEntityManager()
                ->createQuery('SELECT v FROM AppBundle:Vault v WHERE v.code=:code')
                ->setParameter('code',$code)
                ->getResult();
    }
    
    public function findFirstAvailableCodeByValue($code_value){
        return $this->getEntityManager()
                ->createQuery('SELECT v FROM AppBundle:Vault v WHERE v.code_value=:value AND v.id_member is NULL')
                ->setParameter('value', $code_value)
                ->setMaxResults(1)
                ->getOneOrNullResult();
    }
}
