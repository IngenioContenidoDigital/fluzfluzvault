<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use AppBundle\Entity\Vault;
use AppBundle\Entity\Customer;
use AppBundle\Entity\MemberGroup;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class MemberController extends Controller{
    
    
    /** @Route("/member/view", name="listarmiembros")*/
    public function viewMembers(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $em = $this->getDoctrine()->getManager();
            $user=$this->getUser();
            $companyId = $user->getCompany()->getId();
            $company = $em->find('AppBundle\Entity\Company', $companyId);
            $logo = $company->getLogo();

            //$results = $this->getDoctrine()->getRepository('AppBundle:Member')
            //        ->findMembersByCompany($company);
            $results = $company->getGroups();

            $total = count($results);
            
            $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                    ->findCodeValues($company);
            if(count($bonos)<1){
                $bonos=NULL;
            };
            return $this->render('member/listmembers.html.twig',array('groups' => $results,
                'total'=> $total, 'bonos'=>$bonos, 'logo'=>$logo));
        }
    }
    
    /** @Route("/member/create")*/
    public function createMembers(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
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

                    $company->addMember($member);
                    //$member->setCompany($company);

                    $this->getDoctrine()->getManager()->persist($member);
               }else{
                   $list_companies = $member->getCompany();
                   $exists=0;
                   foreach($m as $list_companies){
                       if($m->getId() == $company->getId()){ $exists=1;}
                   }
                   if(!$exists){
                       $member->setCompany($company);
                   }
                   $this->getDoctrine()->getManager()->persist($member);
               }
            }

            // save / write the changes to the database
            $this->getDoctrine()->getManager()->flush();

            /*$results = $this->getDoctrine()->getRepository('AppBundle:Member')
                    ->findAllMembers();*/
            $results = $company->getMembers();
            $total = count($results);
            $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                    ->findCodeValues($company);

            return $this->render('member/listmembers.html.twig',array('members' => $results,
                'total'=> $total, 'bonos'=>$bonos));
        }
    }
    
    /**
     * @Route("/member/detail")
     */
    public function detailMember(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $error= NULL;
            $em = $this->getDoctrine()->getManager();
            $user=$this->getUser();
            $companyId = $user->getCompany()->getId();
            $company = $em->find('AppBundle\Entity\Company', $companyId);
            $logo = $company->getLogo();

            $id_member = $request->query->get('member');

            $member = $em->find('AppBundle\Entity\Member', $id_member);
            $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                    ->findCodeByMember($member);
            $total=count($bonos);

            return $this->render('member/detailmembers.html.twig', array('logo' => $logo, 
                'member' => $member, 
                'total'=>$total, 
                'bonos' => $bonos,
                'error' => $error));
        }
        
    }
    
    
    /** @Route("/member/unique")*/
    public function createUnique(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $error = NULL;
            $duplicates=0;
            $users=0;
            $iterator=0;
            $list_duplicates = array();
            $user=$this->getUser();
            $companyId =  $user->getCompany()->getId();
            $em = $this->getDoctrine()->getManager();

            $company = $em->find('AppBundle\Entity\Company', $companyId);
            $logo = $company->getLogo();

            $form = $this->createFormBuilder()
                ->setMethod('POST')
                ->setAttribute('id', 'member-form')
                ->add('group', TextType::class, array('attr' => array("required"=>true)))
                ->add('member_name', TextType::class, array('attr' => array("required"=>true)))
                ->add('member_email', EmailType::class, array('attr' => array("required"=>true)))
                ->add('mobile_phone', NumberType::class, array('attr' => array("required"=>true)))
                ->add('identification', TextType::class, array('attr' => array("required"=>true)))
                ->add('optional_1', TextType::class, array("required"=>false))
                ->add('optional_2', TextType::class, array("required"=>false))
                ->add('optional_3', TextType::class, array("required"=>false))
                ->add('optional_4', TextType::class, array("required"=>false))
                ->add('optional_5', TextType::class, array("required"=>false))
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                try{
                    $repository = $this->getDoctrine()->getRepository(MemberGroup::class);
                    $group=null;
                    $group = $repository->findOneBy(['name'=> $form['group']->getData()]);
                    if(!isset($group)){
                        $group = new MemberGroup();
                        $group->setName($form['group']->getData());
                        $em->persist($group);
                        $company->addGroup($group);
                    }

                    $member=null;
                    $member = $this->getDoctrine()->getRepository('AppBundle:Member')
                        ->findMember($form['member_email']->getData(),$form['identification']->getData(),$form['mobile_phone']->getData());
                    if (isset($member[0])) {
                        $m = $em->find('AppBundle\Entity\Member', $member[0]->getIdMember());
                        $list_groups = $m->getGroup();
                        $exists=0;
                        foreach($list_groups as $g){
                            if($g->getId() == $group->getId()){ $exists=1;}
                        }
                        if($exists==0){
                            $group->addMember($m);
                            //$company->addMember($m);
                            $this->getDoctrine()->getManager()->persist($m);
                            $users+=1;
                        }else{
                            $duplicates+=1;
                            //$list_duplicates[$iterator] = [$row['member_name'],$row['member_email'],$row['mobile_phone'],$row['identification']];
                            $iterator+=1;
                            $error="El usuario que intentas crear ya existe.";
                        }
                    }else{
                        $member = (new Member())
                            ->setMemberName($form['member_name']->getData())
                            ->setMemberEmail($form['member_email']->getData())
                            ->setMobilePhone($form['mobile_phone']->getData())
                            ->setIdentification($form['identification']->getData())
                            ->setDateAdd(new \DateTime("now"));

                        if($form['optional_1']->getData()!= NULL){$member->setOptional1($form['optional_1']->getData());}
                        if($form['optional_2']->getData()!= NULL){$member->setOptional2($form['optional_2']->getData());}
                        if($form['optional_3']->getData()!= NULL){$member->setOptional3($form['optional_3']->getData());}
                        if($form['optional_4']->getData()!= NULL){$member->setOptional4($form['optional_4']->getData());}
                        if($form['optional_5']->getData()!= NULL){$member->setOptional5($form['optional_5']->getData());}

                        $group->addMember($member);
                        //$company->addMember($member);
                        //$member->setCompany($company);

                        $this->getDoctrine()->getManager()->persist($member);
                    }

                    $this->getDoctrine()->getManager()->flush();
                }catch(Exception $e){
                    $error = isset($error) ? $e->getMessage() : $error;
                }


                /*$results = $this->getDoctrine()->getRepository('AppBundle:Member')
                                    ->findMembersByCompany($company);*/
                $results = $company->getGroups();
                $total = count($results);
                $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                        ->findCodeValues($company);
                if($error!=NULL){
                    return $this->render('member/Create.html.twig', array('error' => $error, 'logo' => $logo, 'form' => $form->createView()));
                }
                return $this->render('member/listmembers.html.twig',array('groups' => $results,
                        'total'=> $total, 'bonos'=>$bonos, 'logo'=>$logo)); 

            }else{
                return $this->render('member/Create.html.twig', array('error' => $error, 'logo' => $logo, 'form' => $form->createView()));
            }
        }
    }
}
