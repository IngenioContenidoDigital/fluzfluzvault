<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Entity\Vault;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use League\Csv\Reader;


class MemberController extends Controller{
    
    
    /** @Route("/member/view")*/
    public function viewMembers(Request $request){
        $results = $this->getDoctrine()->getRepository('AppBundle:Member')
                ->findAllMembers();
        $total = count($results);
        $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                ->findCodeValues();
        
        return $this->render('member/listmembers.html.twig',array('members' => $results,
            'total'=> $total, 'bonos'=>$bonos));
    }
    
    /** @Route("/member/create")*/
    public function createMembers(){
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
                    ->setDateAdd(new \DateTime())
                ;
                $this->getDoctrine()->getManager()->persist($member);
           }
        }

        // save / write the changes to the database
        $this->getDoctrine()->getManager()->flush();
        
        $results = $this->getDoctrine()->getRepository('AppBundle:Member')
                ->findAllMembers();
        $total = count($results);
        $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                ->findCodeValues();
        
        return $this->render('member/listmembers.html.twig',array('members' => $results,
            'total'=> $total, 'bonos'=>$bonos));
    }
}
