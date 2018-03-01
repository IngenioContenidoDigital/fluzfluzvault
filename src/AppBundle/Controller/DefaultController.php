<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use League\Csv\Reader;
use AppBundle\Entity\Member;
use AppBundle\Entity\MemberGroup;
use AppBundle\Entity\Vault;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request){
        // replace this example code with whatever you need
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
                        
            $form = $this->createFormBuilder()
                    ->setAttribute('id', 'myform')
                    ->setAction($this->generateUrl('homepage'))
                    ->setMethod('POST')
                    ->add('group',TextType::class,array('attr' => array(
                        "required"=>true,
                        "placeholder" => "Identificador para este grupo de usuarios"
                    )))
                    ->add('file', FileType::class,array(
                        "attr" =>array("class" => "custom-file-input", "id"=>"file", "required"=>true, 'accept' => ".csv")
                    ));
            $form = $form->getForm();
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                
                $file=$form['file']->getData();
                $ext=$file->guessExtension();
                $file_name=time().".".$ext;
                $file->move("uploads", $file_name);
                
                $reader = Reader::createFromPath($this->get('kernel')->getRootDir().'/../web/uploads/'.$file_name)
                ->setHeaderOffset(0)
                ;
                $group = new MemberGroup();
                $group->setName($form['group']->getData());
                $em->persist($group);
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
                            ->setGroup($group);
                
                        $user=$this->getUser();
                        $companyId = $user->getCompany()->getId();
                        

                        $company = $em->find('AppBundle\Entity\Company', $companyId);
                        $member->setCompany($company);
                        
                        
                        $em->persist($member);
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

            return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'form' => $form->createView(),
            'logo' => $logo
            ]);
        }
        
        
    }
    
    /**
     * @Route("/admin",name="admin")
     */
    public function adminIndex(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            
            $error = $authUtils->getLastAuthenticationError();

            // last username entered by the user
            $lastUsername = $authUtils->getLastUsername();            
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,));
            // get the login error if there is one
        }else{
            return $this->render('admin/admin.html.twig', array(
                'base_dir' => null,
                'error' => null,
                'last_username' => $this->getUser()->getUsername(),
            ));
        }
    }
    
    /**
     * @Route("/report", name="report")
     */
    public function adminReport(Request $request){
        try{
            $error = NULL;
            $result = $this->getDoctrine()
                ->getRepository('AppBundle:Vault')
                ->countAssignedCodes();
        }catch(Exception $e){
            $error = isset($e) ? $e->getMessage() : $error;
        }
        
        return $this->render('admin/report.html.twig', array('error' => $error, 'data' => $result));
    }
}
