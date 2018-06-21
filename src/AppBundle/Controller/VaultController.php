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
use AppBundle\Entity\CompanyEmail;



class VaultController extends Controller{
    /** @Route("/vault/assign")*/
    public function assignToMember(Request $request, \Swift_Mailer $mailer){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                if ($request->isMethod('POST')) {

                $em = $this->getDoctrine()->getManager();
                $user=$this->getUser();
                $companyId = $user->getCompany()->getId();
                $company = $em->find('AppBundle\Entity\Company', $companyId);
                $logo = $company->getLogo();

                $total_asignados=0;
                $valorbonos=0;
                $data =$request->request->all();
                if(isset($data['sms']) && $data['sms']=='on'){
                    $sms=true;
                }else{
                    $sms=false;
                }
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
                            $vault->setAssigneer($user->getUsername());
                            $total_asignados+=1;
                            $bono = $vault->getCode();
                            $fecha = $vault->getExpiration();
                            $valor = $vault->getCodeValue();
                            $valorbonos+=$valor;
                            $cantidad = 1;

                            $this->getDoctrine()->getManager()->persist($vault);
                            $this->getDoctrine()->getManager()->flush();

                            $company1 = $em->find('AppBundle\Entity\Company', $companyId);

                            $template=$em->getRepository(CompanyEmail::class)
                                    ->findTemplateByCompany($company1);

                            if($template==NULL){
                                $body = $this->renderView(
                                            'email/assign.html.twig',
                                            array(
                                                'name' => $name,
                                                'bono' => $bono,
                                                'fecha' => date_format($vault->getExpiration(),'Y-m-d'),
                                                'valor' => $valor,
                                                'cantidad' => $cantidad
                                            )
                                        );
                            }else{
                                $body = $template->getTemplate();
                                $body = str_replace('{logo}', $request->getSchemeAndHttpHost(). '/images/company/'.$logo, $body);
                                $body = str_replace('{date}', date_format($vault->getAssigned(),'Y-m-d'), $body);
                                $body = str_replace('{products}', '<table align="center" border="0.5" cellpadding="0" cellspacing="0" style="width:500px"><tr><td>'.$vault->getCode().'</td><td></td><td>$ '.number_format($vault->getCodeValue(),0,'.',',').'</td><td>1</td><td>$ '.number_format($vault->getCodeValue(),0,'.',',').'</td></tr></table>', $body);
                                $body = str_replace('{total_products}', '$ '.number_format($vault->getCodeValue(),0,'.',','), $body);
                                $body = str_replace('{total_value}', '$ '.number_format($vault->getCodeValue(),0,'.',','), $body);
                                $group = $vault->getgroup();
                                $body = str_replace('{inventory_group}', $group->getName(), $body);
                                $body = str_replace('{name_product}', $group->getName(), $body);
                                $body = str_replace('{member_name}', $member->getMemberName(), $body);
                                $body = str_replace('{member_email}', $member->getMemberEmail(), $body);
                                $body = str_replace('{member_mobile}', $member->getMobilePhone(), $body);
                                $body = str_replace('{member_identification}', $member->getIdentification(), $body);
                                $body = str_replace('{optional_1}', $member->getOptional1(), $body);
                                $body = str_replace('{optional_2}', $member->getOptional2(), $body);
                                $body = str_replace('{optional_3}', $member->getOptional3(), $body);
                                $body = str_replace('{optional_4}', $member->getOptional4(), $body);
                                $body = str_replace('{optional_5}', $member->getOptional5(), $body);
                                $body = str_replace('{expiration}', date_format($vault->getExpiration(),'Y-m-d'), $body);
                                $body = str_replace('{bono}', $vault->getCode(), $body);
                                $body = str_replace('{bono_value}', $vault->getCodeValue(), $body);
                                $body = str_replace('{bono_value_currency}', '$ '.number_format($vault->getCodeValue(),0,'.',','), $body);
                                $body = str_replace('{quantity}', '1', $body);
                            }


                            $message = (new \Swift_Message('Bono de '.$company->getName()))
                                    ->setFrom('boveda@fluzfluz.com')
                                    ->setTo($member->getMemberEmail())
                                    ->setBody(
                                            $body,
                                            'text/html'
                                    );

                            try{
                                $response = $mailer->send($message);
                                if($sms){
                                    $sms_txt ='Hola '.$member->getMemberName().'%0A%0A'.$company->getName().' hace entrega de un bono '.$group->getName().' por valor de $'.number_format($vault->getCodeValue(),0,'.',',').' Bono: '.$vault->getCode();
                                    $sms_url = urlencode($sms_txt);
                                    
                                    
                                    $url = 'https://api.masivapp.com/SmsHandlers/sendhandler.ashx?action=sendmessage&username=Api_A91ON&password=WBZ45NA8Z3&recipient=+57'.$member->getMobilePhone()."&messagedata=".$sms_url."&longMessage=true";

                                    /*$curl = curl_init();
                                    $headers = array(
                                            "Content-Type: text/xml; charset=utf-8"
                                    );
                                    curl_setopt($curl,CURLOPT_HTTPHEADER, $headers);
                                    curl_setopt($curl, CURLOPT_URL, $url);
                                    $response = curl_exec($curl);
                                    curl_close($curl);*/

                                    $args = array ('username'=>'Api_A91ON', 'password'=>'WBZ45NA8Z3');
                                    $opts = array(
                                      'http' => array(
                                        'method'=>'POST', 
                                        'header'=>'Content-Type: application/xml', 
                                        'content'=>http_build_query($args)
                                      )
                                    );

                                    $context = stream_context_create($opts);
                                    $result = file_get_contents($url, false, $context);

                                    $xml=simplexml_load_string($result);
                                    $items   = $xml->data;
                                    $response = json_decode(json_encode($items), TRUE)['acceptreport'];
                                    $result = ( $response['statuscode'] == 0 ) ? true : false;    
                                }
                            }catch(Exception $e){
                                $response = $e->getMessage();
                            }
                        }
                    }
                }
                return $this->render('vault/results.html.twig',array('bonosasignados'=>$total_asignados, 'valortotal'=>$valorbonos, 'logo' => $logo));
            }else{
                    return new Response("<div>Error. Nada que Mostrar</div>");
                }
            }else{
                return new Response("<div>Usuario no autenticado</div>");
            }
        }
    }
    
    /** @Route("/vault/view")*/
    public function viewTemplate(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $total_asignados=150;
            $valorbonos=1500000;
            return $this->render('vault/results.html.twig',array('bonosasignados'=>$total_asignados, 'valortotal'=>$valorbonos));
        }
    }
    
    /** @Route("/vault/load") */
    public function vaultLoad(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
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
                ->add('delimiter',
                        ChoiceType::class,
                        array(
                        'choices' => array(
                            'Coma ( , )' => ',',
                            'Punto y Coma ( ; )' => ';',
                            'Pipe ( | )' => '|'/*,
                            'Espacio' => "\u{0020}",
                            'Tab' => "\u{0009}"*/),
                        'multiple'=>false,
                        'expanded'=>true,
                        'data' => ';'
                        )     
                )
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

                    $valid_ext = array('csv', 'txt');
                    $valid_header = array("code","code_value","expiration");
                    if(in_array($ext, $valid_ext)){
                        $reader = Reader::createFromPath($this->get('kernel')->getRootDir().'/../web/inventory/'.$file_name);
                        $reader->setDelimiter($form['delimiter']->getData());
                        $reader->setEnclosure('"');
                        $reader->setHeaderOffset(0);
                        $header = $reader->getHeader();
                        if(in_array("code", $header) && in_array("code_value", $header) && in_array("expiration", $header)){
                            $em = $this->getDoctrine()->getManager();
                            $repository = $this->getDoctrine()->getRepository(VaultGroup::class);
                            $group=null;
                            $group = $repository->findOneBy(['name'=> $form['group']->getData()]);
                            if(!isset($group)){
                                $group = new VaultGroup();
                                $group->setName($form['group']->getData());
                                $em->persist($group);
                            }
                            $duplicates=0;                        
                            $records = $reader->getRecords();
                            foreach ($records as $offset => $row) {

                                $vault = $this->getDoctrine()->getRepository('AppBundle:Vault')
                                    ->findByCode($row['code']);
                                $total=count($vault);

                                if ($total == 0) {

                                    $tz = new \DateTimeZone('America/Bogota');
                                    //$date->setTimezone($tz);
                                    $date = \DateTime::createFromFormat('d/m/Y H:i:s', $row['expiration'], $tz);
                                    //$date->createFromFormat();
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
                            return $this->render('vault/inventoryConfirm.html.twig',array('error'=>$error));
                        }else{
                            $error = "La Estructura del Archivo CSV NO es válida. Por favor revisa el archivo que intentaste cargar.";
                        }
                    }else{
                        $error = "Extensión del archivo no válida";
                    }
                }catch(\League\Csv\Exception $e){
                    $error = isset($error) ? $e->getMessage() : $error;
                }
                return $this->render('vault/inventoryUpload.html.twig',array('error'=>$error, 'form'=>$form->createView()));
            }else{
                return $this->render('vault/inventoryUpload.html.twig',array('error'=>$error, 'form'=>$form->createView()));
            }
        }
    }
    
    /**
     * @Route("/inventory")
     */
    public function vaultInventory(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $error = NULL;
            $em = $this->getDoctrine()->getManager();
            $user=$this->getUser();
            $companyId = $user->getCompany()->getId();
            $company = $em->find('AppBundle\Entity\Company', $companyId);
            $logo = $company->getLogo();

            $result = $this->getDoctrine()->getRepository('AppBundle:Vault')
                    ->inventory($company);
            return $this->render('vault/inventory.html.twig', array('error' => $error, 'data' => $result, 'logo' => $logo));
        }
    }
    
    /**
     * @Route("/vault/print")
     */
    public function vaultPrint(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new Response('<div>Forbidden</div>');
        }else{
            $em = $this->getDoctrine()->getManager();
            $user=$this->getUser();
            $companyId = $user->getCompany()->getId();
            $company = $em->find('AppBundle\Entity\Company', $companyId);
            $logo = $company->getLogo();

            $id_member = $request->query->get('member');
            $code = $request->query->get('code');

            $member = $em->find('AppBundle\Entity\Member', $id_member);
            $bono = $em->find('AppBundle\Entity\Vault',$code);


            $snappy = $this->get('knp_snappy.pdf');
            $snappy->setOption('no-outline', true);
            $snappy->setOption('orientation', 'landscape');
            $snappy->setOption('page-size','A6');
            $snappy->setOption('encoding', 'UTF-8');
            $filename = 'Fluz Fluz Vault Printed Record';

            $html = $this->renderView('vault/vaultPrint.html.twig', array(
                'logo' => $logo,
                'bono' => $bono,
                'member' => $member
            ));


            return new Response(
                $snappy->getOutputFromHtml($html),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$filename.'.pdf"'
                )
            );
        }
    }

    /**
     * @Route("/vault/send")
     */
    public function vaultSend(Request $request, \Swift_Mailer $mailer){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return new Response('<div>Forbidden</div>');
        }else{
            $error=NULL;
            $em = $this->getDoctrine()->getManager();
            $user=$this->getUser();
            $companyId = $user->getCompany()->getId();
            $company = $em->find('AppBundle\Entity\Company', $companyId);
            $logo = $company->getLogo();

            $id_member = $request->query->get('member');
            $code = $request->query->get('code');

            $member = $em->find('AppBundle\Entity\Member', $id_member);
            $bono = $em->find('AppBundle\Entity\Vault',$code);
            
            $template=$em->getRepository(CompanyEmail::class)
                    ->findTemplateByCompany($company);
            
            if($template==NULL){
                $body = $this->renderView(
                            'email/resend.html.twig',
                            array(
                                'logo' => $logo,
                                'member' => $member,
                                'bono' => $bono,
                                'error' => $error
                            )
                        );
            }else{
                $body = $template->getTemplate();
                $body = str_replace('{logo}', $request->getSchemeAndHttpHost(). '/images/company/'.$logo, $body);
                $body = str_replace('{date}', date_format($bono->getAssigned(),'Y-m-d'), $body);
                $body = str_replace('{products}', '<table align="center" border="0.5" cellpadding="0" cellspacing="0" style="width:500px"><tr><td>'.$bono->getCode().'</td><td></td><td>$ '.number_format($bono->getCodeValue(),0,'.',',').'</td><td>1</td><td>$ '.number_format($bono->getCodeValue(),0,'.',',').'</td></tr></table>', $body);
                $body = str_replace('{total_products}', '$ '.number_format($bono->getCodeValue(),0,'.',','), $body);
                $body = str_replace('{total_value}', '$ '.number_format($bono->getCodeValue(),0,'.',','), $body);
                $group = $bono->getgroup();
                $body = str_replace('{inventory_group}', $group->getName(), $body);
                $body = str_replace('{name_product}', $group->getName(), $body);
                $body = str_replace('{member_name}', $member->getMemberName(), $body);
                $body = str_replace('{member_email}', $member->getMemberEmail(), $body);
                $body = str_replace('{member_mobile}', $member->getMobilePhone(), $body);
                $body = str_replace('{member_identification}', $member->getIdentification(), $body);
                $body = str_replace('{optional_1}', $member->getOptional1(), $body);
                $body = str_replace('{optional_2}', $member->getOptional2(), $body);
                $body = str_replace('{optional_3}', $member->getOptional3(), $body);
                $body = str_replace('{optional_4}', $member->getOptional4(), $body);
                $body = str_replace('{optional_5}', $member->getOptional5(), $body);
                $body = str_replace('{expiration}', date_format($bono->getExpiration(),'Y-m-d'), $body);
                $body = str_replace('{bono}', $bono->getCode(), $body);
                $body = str_replace('{bono_value_currency}', '$ '.number_format($bono->getCodeValue(),0,'.',','), $body);
                $body = str_replace('{bono_value}', $bono->getCodeValue(), $body);
                $body = str_replace('{quantity}', '1', $body);
            }
                
            
            $message = (new \Swift_Message('Bono de '.$company->getName()))
                    ->setFrom('boveda@fluzfluz.com')
                    ->setTo($member->getMemberEmail())
                    ->setBody(
                            $body,
                            'text/html'
                    );

            try{           
                $response = $mailer->send($message);
            }catch(Exception $e){
                $response = $e->getMessage();
            }
            return new Response($response);
        }
    }
}
