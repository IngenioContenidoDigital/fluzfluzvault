<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository{
    public function findAll(){
        return $this->getEntityManager()
                ->createQuery('SELECT c FROM AppBundle:Company c')
                ->getResult();
    }
    
    public function listCompanies(){
        return $this->getEntityManager()
                ->createQuery('SELECT DISTINCT c.id id, c.name name FROM AppBundle:Company c')
                ->getResult();
    }
    
     public function findById($id_company){
        return $this->getEntityManager()
                ->createQuery('SELECT c FROM AppBundle:Company c WHERE c.id=:id')
                ->setParameter('id',$id_company)
                ->getResult();
    }
}
