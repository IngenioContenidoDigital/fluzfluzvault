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
                ->createQuery('SELECT m FROM AppBundle:Member m WHERE m.id_member=:id')
                ->setParameter('id',$id_member)
                ->getResult();
    }
    
    public function findAllMembers(){
        return $this->getEntityManager()
            ->createQuery(
                'SELECT m FROM AppBundle:Member m LEFT JOIN AppBundle:Vault v WITH v.id_member=m.id_member WHERE v.id_member IS NULL ORDER BY m.id_member ASC'
            )
            ->getResult();
    }
}
