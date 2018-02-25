<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CompanyRepository extends EntityRepository{
    public function findAll(){
        return $this->getEntityManager()
                ->createQuery('SELECT c FROM AppBundle:Company c')
                ->getResult();
    }
}
