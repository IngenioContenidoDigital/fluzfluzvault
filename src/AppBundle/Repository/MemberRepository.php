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
        //LEFT JOIN AppBundle:Vault v WITH v.member_id=m.id WHERE v.member_id IS NULL 
        return $this->getEntityManager()
            ->createQuery(
                'SELECT m FROM AppBundle:Member m ORDER BY m.id ASC'
            )
            ->getResult();
    }
}
