<?php

// src/AppBundle/Repository/CustomerRepository.php
namespace AppBundle\Repository;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\EntityRepository;


class MemberRepository extends EntityRepository{
    public function findMember($member_email, $mobile_phone, $identification){
        return $this->getEntityManager()
                ->createQuery('SELECT m FROM AppBundle:Member m WHERE m.member_email=:email OR m.mobile_phone=:phone OR m.identification=:identification')
                ->setParameter('email',$member_email)
                ->setParameter('phone', $mobile_phone)
                ->setParameter('identification', $identification)
                ->getResult();
    }
    
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
                ->distinct()
                ->from('AppBundle:Member','m')
                ->innerJoin('m.group', 'g')
                ->where('g.company = :company')
                ->setParameter('company', $company)
                ->getQuery()
                ->getResult();
    }
}
