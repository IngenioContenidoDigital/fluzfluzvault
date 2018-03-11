<?php

// src/AppBundle/Repository/CustomerRepository.php
namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;


class VaultRepository extends EntityRepository{
    public function findCodeValues($company){
        return $this->getEntityManager()->createQueryBuilder()
                ->select('v.code_value, g.name')
                ->distinct('v.code_value, g.name')
                ->from('AppBundle:Vault', 'v')
                ->leftJoin('v.group', 'g')
                ->where('v.members IS NULL AND v.company=:company')
                ->setParameter('company', $company)
                ->getQuery()
                ->getResult();
    }
    
    public function findByCode($code){
        return $this->getEntityManager()
                ->createQuery('SELECT v FROM AppBundle:Vault v WHERE v.code=:code')
                ->setParameter('code',$code)
                ->getResult();
    }
    
    public function findFirstAvailableCodeByValue($code_value, $company){
        return $this->getEntityManager()
                ->createQuery('SELECT v FROM AppBundle:Vault v WHERE v.code_value=:value AND v.members is NULL AND v.company=:company')
                ->setParameter('value', $code_value)
                ->setParameter('company', $company)
                ->setMaxResults(1)
                ->getOneOrNullResult();
    }
    
    public function countAssignedCodes(){
        $query = $this->createQueryBuilder('v')
        ->select('count(v.id) total, sum(v.code_value) value, DATE_FORMAT(v.assigned, \'%Y-%m-%d\') date')
        ->innerJoin('v.members', 'm')
        ->groupBy('date')
        ->orderBy('date')
        //->where('r.foo = :parameter')
        //->setParameter('parameter', $parameter)
        ->getQuery();

        return $query->getResult(); 
    }
    
    public function inventory($company){
        return $this->getEntityManager()->createQueryBuilder()
                ->select('g.name, v.code_value, count(v) total')
                ->from('AppBundle:Vault', 'v')
                ->leftJoin('v.group', 'g')
                ->groupBy('g.name, v.code_value')
                ->where('v.members IS NULL AND v.company=:company')
                ->setParameter('company', $company)
                ->getQuery()
                ->getResult();
    }
    
    public function findCodeByMember($member){
        return $this->getEntityManager()->createQueryBuilder()
                ->select('v')
                ->from('AppBundle:Vault', 'v')
                ->where('v.members =:member')
                ->setParameter('member', $member)
                ->getQuery()
                ->getResult();
    }
    
}
