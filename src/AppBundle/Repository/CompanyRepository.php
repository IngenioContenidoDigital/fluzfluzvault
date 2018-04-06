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
    
    public function findCompanyGroups($company){
        return $this->getEntityManager()->createQueryBuilder()
                ->select('g.id, g.name, COUNT(m.id) AS members')
                ->from('AppBundle:Member','m')
                ->innerJoin('m.group', 'g')
                ->where('m.company = :company')
                ->setParameter('company', $company)
                ->groupBy('g.id, g.name')
                ->getQuery()
                ->getResult();
    }
}
