<?php

// src/AppBundle/Repository/CustomerRepository.php
namespace AppBundle\Repository;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\EntityRepository;


class MemberRepository extends EntityRepository{
    public function findMemberByEmail($member_email){
        return $this->getEntityManager()
                ->createQuery('SELECT m FROM AppBundle:Member m WHERE m.member_email=:email')
                ->setParameter('email',$member_email)
                ->getResult();
    }
    
    public function findMemberById($id_member){
        return $this->getEntityManager()
                ->createQuery('SELECT m FROM AppBundle:Member m WHERE m.id=:id')
                ->setParameter('id',$id_member)
                ->getResult();
    }
    
    public function findAllMembers(){
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AppBundle:Member m')
            ->getResult();
    }
    
    public function findMembersByCompany($company){
        return $this->getEntityManager()->createQueryBuilder()
                ->select('m, g')
                ->from('AppBundle:Member','m')
                ->innerJoin('m.group', 'g')
                ->where('m.company = :company')
                ->setParameter('company', $company)
                ->getQuery()
                ->getResult();
    }
}
