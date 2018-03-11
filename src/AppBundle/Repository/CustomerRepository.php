<?php
// src/AppBundle/Repository/CustomerRepository.php
namespace AppBundle\Repository;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class CustomerRepository extends EntityRepository implements UserLoaderInterface{
    public function loadUserByUsername($username){
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findAll(){
        return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('c, x')
                ->from('AppBundle:Customer','c')
                ->innerJoin('c.company', 'x')
                ->getQuery()
                ->getResult();
                
    }
    
    public function findById($id_customer){
        return $this->getEntityManager()
                ->createQueryBuilder()
                ->select('c, x')
                ->from('AppBundle:Customer','c')
                ->innerJoin('c.company', 'x')
                ->where('c.id_customer = :id')
                ->setParameter('id', $id_customer)
                ->getQuery()
                ->getOneOrNullResult();
    }
}