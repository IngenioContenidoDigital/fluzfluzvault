<?php

namespace AppBundle\Repository;

/**
 * CompanyEmailRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CompanyEmailRepository extends \Doctrine\ORM\EntityRepository{
    public function findTemplateByCompany($company){
        try{
            $result = $this->getEntityManager()->createQueryBuilder()
                ->select('t')
                ->from('AppBundle:CompanyEmail', 't')
                ->where('t.company =:company')
                ->setParameter('company', $company)
                ->getQuery()
                ->getOneOrNullResult();
        }catch(Exception $e){
            $result = $e->getMessage();
        }
        return $result;
    }
}