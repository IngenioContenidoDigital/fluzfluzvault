<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use League\Csv\Reader;

use AppBundle\Entity\Vault;
use AppBundle\Entity\Member;
use AppBundle\Entity\VaultGroup;
use AppBundle\Entity\Company;



class VaultController extends Controller{
    /** @Route("/vault/assign")*/
    public function assignToMember(Request $request, \Swift_Mailer $mailer){
         if ($request->isMethod('POST')) {
             
            $em = $this->getDoctrine()->getManager();
            $user=$this->getUser();
            $companyId = $user->getCompany()->getId();
            $company = $em->find('AppBundle\Entity\Company', $companyId);
            $logo = $company->getLogo();
             
             $total_asignados=0;
             $valorbonos=0;
            $data =$request->request->all();
            foreach($data as $k /*member*/ => $d /*value*/){
                $vault = $this->getDoctrine()->getRepository(Vault::class)
                            ->findFirstAvailableCodeByValue($data['tipo-bono'],$company);

                if(isset($vault)){
                    if (is_numeric($k)){
                        $member = $this->getDoctrine()->getRepository(Member::class)
                                ->find((int)$k);
                        $id = $member->getIdMember();
                        $email = $member->getMemberEmail();
                        $name = $member->getMemberName();

                        $vault->setMember($member);
                        $vault->setAssigned(new \DateTime("now"));
                        $total_asignados+=1;
                        $bono = $vault->getCode();
                        $fecha = $vault->getExpiration();
                        $valor = $vault->getCodeValue();
                        $valorbonos+=$valor;
                        $cantidad = 1;

                        $this->getDoctrine()->getManager()->persist($vault);
                        $this->getDoctrine()->getManager()->flush();



                    /*$message = (new \Swift_Message('Bono de Bienvenida Credencial – Bodytech'))
                                ->setFrom('boveda@fluzfluz.com')
                                ->setTo($email)
                                ->setBody(
                                    $this->renderView(
                                        // app/Resources/views/email/assign.html.twig
                                        'email/assign.html.twig',
                                        array(
                                            'name' => $name,
                                            'bono' => $bono,
                                            'fecha' => $fecha,
                                            'valor' => $valor,
                                            'cantidad' => $cantidad
                                        )
                                    ),
                                    'text/html'
                                );

                        $mailer->send($message);*/
                    }
                }
            }
            return $this->render('vault/results.html.twig',array('bonosasignados'=>$total_asignados, 'valortotal'=>$valorbonos, 'logo' => $logo));
        }else{
            return new Response("<div>Error. Nada que Mostrar</div>");
        }
    }
    
    /** @Route("/vault/view")*/
    public function viewTemplate(){
        $total_asignados=150;
        $valorbonos=1500000;
        return $this->render('vault/results.html.twig',array('bonosasignados'=>$total_asignados, 'valortotal'=>$valorbonos));
    }
    
    /** @Route("/vault/load") */
    public function vaultLoad(Request $request){
        $error = NULL;
        $list_companies = $this->getDoctrine()->getRepository('AppBundle:Company')
                ->listCompanies();
        $user=$this->getUser();
        $companyId = $user->getCompany()->getId();

            $opciones = array();
            
            if(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
                foreach($list_companies as $company){
                    $opciones = array_merge($opciones,array($company['name'] => $company['id']));
                }
            }else{
                foreach($list_companies as $company){
                    if($companyId==$company['id']){
                     $opciones = array_merge($opciones,array($company['name'] => $company['id']));   
                    }
                }
            }
            
        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->setAction('/vault/load')
            ->setAttribute('id', 'vault-upload')
            ->add('group', TextType::class)
            ->add('vault', FileType::class, array('attr' => array("required"=>true)))
            ->add('company', ChoiceType::class, array(
                'choices' => $opciones,
                'placeholder' => ' -- Elija una Empresa --',
                'required' => true
            ))
            ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            try{
                $file=$form['vault']->getData();
                $ext=$file->guessExtension();
                $file_name=time().".".$ext;
                $file->move("inventory", $file_name);
                
                $reader = Reader::createFromPath($this->get('kernel')->getRootDir().'/../web/inventory/'.$file_name)
                ->setHeaderOffset(0)
                ;
                $em = $this->getDoctrine()->getManager();
                $group = new VaultGroup();
                $group->setName($form['group']->getData());
                $em->persist($group);
                foreach ($reader as $row) {
                    $vault = $this->getDoctrine()->getRepository('AppBundle:Vault')
                        ->findByCode($row['code']);
                    $total=count($vault);
                    
                    if ($total == 0) {
                        
                        $date = new \DateTime();
                        $tz = new \DateTimeZone('America/Bogota');
                        $date = $date->createFromFormat('d/m/Y H:i:s', $row['expiration']);
                        $date->setTimezone($tz);
                        $vault = (new Vault())
                            ->setCode($row['code'])
                            ->setCodeValue($row['code_value'])
                            ->setExpiration($date)
                            ->setGroup($group);
                        
                        $company = $em->find('AppBundle\Entity\Company', $form['company']->getData());
                        $vault->setCompany($company);
                        $em->persist($vault);
                   }
                }
                $this->getDoctrine()->getManager()->flush();
            }catch(Exception $e){
                $error = isset($error) ? $e->getMessage() : $error;
            }
            return new Response('<p>Se ha realizado el proceso de carga de los Inventarios</p>');
            //return $this->render('admin/company/companyCreate.html.twig', array('error' => $error, 'form' => $form->createView()));
        }else{
            return $this->render('vault/inventoryUpload.html.twig',array('error'=>$error, 'form'=>$form->createView()));
        }
    }
}
