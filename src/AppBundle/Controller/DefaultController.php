<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        $error = NULL;
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
                    ->add('delimiter',ChoiceType::class,
                            array(
                            'choices' => array(
                                ',' => ',',
                                ';' => ';',
                                '|' => '|'/*,
                                'Espacio' => "\u{0020}",
                                'Tab' => "\u{0009}"*/),
                            'choices_as_values' => true,
                            'multiple'=>false,
                            'expanded'=>true,
                            'data' => ';'
                            )     
                    )
                    ->add('file', FileType::class,array(
                        "attr" =>array("class" => "custom-file-input", "id"=>"file", "required"=>true, 'accept' => ".csv")
                    ));
            $form = $form->getForm();
            $form->handleRequest($request);
            
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                $user=$this->getUser();
                $companyId = $user->getCompany()->getId();

                $company = $em->find('AppBundle\Entity\Company', $companyId);
                
                $file=$form['file']->getData();
                $ext=$file->guessExtension();
                $valid_ext = array('csv', 'txt');
                $file_name=time().".".$ext;
                $file->move("uploads", $file_name);
                $valid_header = array("member_name","member_email","mobile_phone","identification");
                if(in_array($ext, $valid_ext)){
                    $reader = Reader::createFromPath($this->get('kernel')->getRootDir().'/../web/uploads/'.$file_name,'r');
                    $reader->setDelimiter($form['delimiter']->getData());
                    $reader->setEnclosure('"');
                    $reader->setHeaderOffset(0);
                    $header = $reader->getHeader();
                    if(in_array("member_name", $header) && in_array("member_email", $header) && in_array("mobile_phone", $header) && in_array("identification", $header)){
                        $repository = $this->getDoctrine()->getRepository(MemberGroup::class);
                        $group=null;
                        $group = $repository->findOneBy(['name'=> $form['group']->getData()]);
                        if(!isset($group)){
                            $group = new MemberGroup();
                            $group->setName($form['group']->getData());
                            $em->persist($group);
                        }
                        $duplicates=0;
                        $records = $reader->getRecords();
                        foreach ($records as $offset => $row) {
                            $member=null;
                            $member = $this->getDoctrine()->getRepository('AppBundle:Member')
                                ->findMember($row['member_email'],$row['identification'],$row['mobile_phone']);
                            if (isset($member[0])) {
                                $duplicates+=1;
                            }else{
                                $member = (new Member())
                                    ->setMemberName($row['member_name'])
                                    ->setMemberEmail($row['member_email'])
                                    ->setMobilePhone($row['mobile_phone'])
                                    ->setIdentification($row['identification'])
                                    ->setDateAdd(new \DateTime("now"))
                                    ->setGroup($group);
                                if($row['optional_1']!=NULL){$member->setOptional1($row['optional_1']);}
                                if($row['optional_2']!=NULL){$member->setOptional2($row['optional_2']);}
                                if($row['optional_3']!=NULL){$member->setOptional3($row['optional_3']);}
                                if($row['optional_4']!=NULL){$member->setOptional4($row['optional_4']);}
                                if($row['optional_5']!=NULL){$member->setOptional5($row['optional_5']);}
                                $member->setCompany($company);
                                $em->persist($member);
                            }
                        }
                        $this->getDoctrine()->getManager()->flush();

                        //$results = $this->getDoctrine()->getRepository('AppBundle:Member')
                        //        ->findAllMembers();
                        $results = $this->getDoctrine()->getRepository('AppBundle:Member')
                                ->findMembersByCompany($company);
                        $total = count($results);
                        $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                                ->findCodeValues($company);

                        return $this->render('member/listmembers.html.twig',array('members' => $results,
                            'total'=> $total, 'bonos'=>$bonos, 'logo'=>$logo)); 
                    }else{
                        $error = "La Estructura del Archivo CSV NO es válida. Por favor revisa el archivo que intentaste cargar.";
                    }
                }else{
                    $error = "Extensión del archivo no válida";
                }
                return $this->render('default/index.html.twig', [
                'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                'form' => $form->createView(),
                'logo' => $logo,
                'error' => $error
                ]);
            }

            return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'form' => $form->createView(),
            'logo' => $logo,
            'error' => $error
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
