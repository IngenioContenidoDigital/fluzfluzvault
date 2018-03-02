<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Entity\Vault;
use AppBundle\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\Response;


class MemberController extends Controller{
    
    
    /** @Route("/member/view", name="listarmiembros")*/
    public function viewMembers(Request $request){
        
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $companyId = $user->getCompany()->getId();
        $company = $em->find('AppBundle\Entity\Company', $companyId);
        $logo = $company->getLogo();
        
        $results = $this->getDoctrine()->getRepository('AppBundle:Member')
                ->findMembersByCompany($company);
        
        $total = count($results);
        $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                ->findCodeValues($company);
        if(count($bonos)<1){
            $bonos=NULL;
        };
        return $this->render('member/listmembers.html.twig',array('members' => $results,
            'total'=> $total, 'bonos'=>$bonos, 'logo'=>$logo));
    }
    
    /** @Route("/member/create")*/
    public function createMembers(Request $request){
        
        $user=$this->getUser();
        $companyId =  $user->getCompany()->getId();
        $em = $this->getDoctrine()->getManager();

        $company = $em->find('AppBundle\Entity\Company', $companyId);
        
        $reader = Reader::createFromPath($this->get('kernel')->getRootDir().'/../web/uploads/members.csv')
                ->setHeaderOffset(0)
        ;
        foreach ($reader as $row) {
            $member = $this->getDoctrine()->getRepository('AppBundle:Member')
                ->findMemberByEmail($row['member_email']);
            $total=count($member);
                
            if ($total == 0) {
                $member = (new Member())
                    ->setMemberName($row['member_name'])
                    ->setMemberEmail($row['member_email'])
                    ->setMobilePhone($row['mobile_phone'])
                    ->setIdentification($row['identification'])
                    ->setDateAdd(new \DateTime("now"))
                ;
                
                
                $member->setCompany($company);
                
                $this->getDoctrine()->getManager()->persist($member);
           }
        }

        // save / write the changes to the database
        $this->getDoctrine()->getManager()->flush();
        
        $results = $this->getDoctrine()->getRepository('AppBundle:Member')
                ->findAllMembers();
        $total = count($results);
        $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                ->findCodeValues($company);
        
        return $this->render('member/listmembers.html.twig',array('members' => $results,
            'total'=> $total, 'bonos'=>$bonos));
    }
}
