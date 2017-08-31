<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MemberController extends Controller{
    /** @Route("/member/view")*/
    public function viewMembers(){
        $results = $this->getDoctrine()->getRepository('AppBundle:Member')
                ->findAllMembers();
        return $this->render('member/listmembers.html.twig',array('members' => $results));
    }
}
