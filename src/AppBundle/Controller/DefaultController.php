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
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ComboChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\GaugeChart;
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
                $list_duplicates = array();
                try{
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
                            $iterator=0;
                            $users=0;
                            $records = $reader->getRecords();
                            foreach ($records as $offset => $row) {
                                $member=null;
                                $member = $this->getDoctrine()->getRepository('AppBundle:Member')
                                    ->findMember($row['member_email'],$row['identification'],$row['mobile_phone']);
                                if (isset($member[0])) {
                                    $m = $em->find('AppBundle\Entity\Member', $member[0]->getIdMember());
                                    $list_companies = $m->getCompany();
                                    $exists=0;
                                    foreach($list_companies as $c){
                                        if($c->getId() == $companyId){ $exists=1;}
                                    }
                                    if($exists==0){
                                        $company->addMember($m);
                                        $this->getDoctrine()->getManager()->persist($m);
                                        $users+=1;
                                    }else{
                                        $duplicates+=1;
                                        $list_duplicates[$iterator] = [$row['member_name'],$row['member_email'],$row['mobile_phone'],$row['identification']];
                                        $iterator+=1;
                                    }
                                }else{
                                    $member = (new Member())
                                        ->setMemberName($row['member_name'])
                                        ->setMemberEmail($row['member_email'])
                                        ->setMobilePhone($row['mobile_phone'])
                                        ->setIdentification($row['identification'])
                                        ->setDateAdd(new \DateTime("now"))
                                        ->setGroup($group);
                                    if(isset($row['optional_1'])){$member->setOptional1($row['optional_1']);}
                                    if(isset($row['optional_2'])){$member->setOptional2($row['optional_2']);}
                                    if(isset($row['optional_3'])){$member->setOptional3($row['optional_3']);}
                                    if(isset($row['optional_4'])){$member->setOptional4($row['optional_4']);}
                                    if(isset($row['optional_5'])){$member->setOptional5($row['optional_5']);}
                                    $company->addMember($member);
                                    //$member->setCompany($company);
                                    $em->persist($member);
                                    $users+=1;
                                }
                            }
                            $this->getDoctrine()->getManager()->flush();

                            //$results = $this->getDoctrine()->getRepository('AppBundle:Member')
                            //        ->findAllMembers();
                            /*$results = $this->getDoctrine()->getRepository('AppBundle:Member')
                                    ->findMembersByCompany($company);*/
                            $results = $company->getMembers();
                            $total = count($results);
                            $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                                    ->findCodeValues($company);
                            
                            return $this->render('member/results.html.twig',array('duplicates' => $duplicates,
                                'users'=> $users, 'lista_duplicados'=>$list_duplicates, 'logo'=>$logo)); 
                        }else{
                            $error = "La Estructura del Archivo CSV NO es válida. Por favor revisa el archivo que intentaste cargar.";
                        }
                    }else{
                        $error = "Extensión del archivo no válida";
                    }
                }catch(\League\Csv\Exception $e){
                    $error = $e->getMessage();
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
            
            //$error = $authUtils->getLastAuthenticationError();

            // last username entered by the user
            //$lastUsername = $authUtils->getLastUsername();            
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
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
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
    
    /**
     * @Route("/customer/report", name="customer_report")
     */
    public function customerReport(Request $request){
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
                $conn = $em->getConnection();
                $qmeses = "SELECT 
    YEAR(v.assigned) AS anio,
    MONTH(v.assigned) AS mes,
    MONTHNAME(v.assigned) AS nombre_mes
    FROM
    vault AS v
    WHERE v.company_id=".$companyId." AND v.assigned IS NOT NULL
    GROUP BY MONTH(v.assigned)
    ORDER BY YEAR(v.assigned) ASC, MONTH(v.assigned) ASC";

                $meses= $conn->query($qmeses)->fetchAll();
                $qgrupos ="SELECT DISTINCT vg.`name` AS `grupo_inventario`
    FROM
    vault AS v
    LEFT JOIN vault_group AS vg ON v.vault_group_id = vg.id
    WHERE v.company_id=".$companyId;

                $grupos = $conn->query($qgrupos)->fetchAll();
                $i=0;
                $titulo=array('Mes');
                $tgrupos=0;
                foreach($grupos as $kk => $vv){
                    array_push($titulo,$vv['grupo_inventario']);
                    $tgrupos+=1;
                }
                array_push($titulo,'Total');
                $resultados[$i]=$titulo;
            $i+=1;
            foreach($meses as $key => $value){
                $query="
    SELECT vg.`name` AS grupo_inventario, 
    (SELECT IFNULL(Count(v1.`code`),0) FROM vault AS v1 
    INNER JOIN vault_group AS vg1 ON vg1.id=v1.vault_group_id 
    WHERE v1.company_id=".$companyId." AND v1.assigned IS NOT NULL AND YEAR(v1.assigned)=".(int)$value['anio']." AND MONTH(v1.assigned)=".(int)$value['mes']." AND vg1.id=vg.id) AS bonos
    FROM vault_group AS vg 
    LEFT JOIN vault AS v ON v.vault_group_id=vg.id
    WHERE v.company_id =".$companyId."
    GROUP BY vg.`name`";

                $fila = $conn->query($query)->fetchAll();
                $datos=array($value['mes']." - ".$value['nombre_mes'] );
                $tbonos=0;
                foreach($fila as $k => $v){
                    $tbonos+=(int)$v['bonos'];
                    array_push($datos,(int)$v['bonos']);
                }
                array_push($datos,$tbonos);
                $resultados[$i]=$datos;
                $i+=1;
            }    

            $Combo = new ComboChart();
            $Combo->getData()->setArrayToDataTable($resultados);
            $Combo->getOptions()->setTitle('Reporte Códigos Entregados');
            $Combo->getOptions()->setHeight(350);
            $Combo->getOptions()->setWidth(650);
            $Combo->getOptions()->getTitleTextStyle()->setBold(true);
            $Combo->getOptions()->getTitleTextStyle()->setColor('#000');
            $Combo->getOptions()->getTitleTextStyle()->setItalic(true);
            $Combo->getOptions()->getTitleTextStyle()->setFontName('Arial');
            $Combo->getOptions()->getTitleTextStyle()->setFontSize(10);
            $Combo->getOptions()->getVAxis()->setTitle('Bonos Asignados');
            $Combo->getOptions()->getHAxis()->setTitle('Mes');
            $Combo->getOptions()->setSeriesType('bars');
            $Combo->getOptions()->setColors(['#e0440e', '#e6693e', '#ec8f6e', '#f3b49f', '#f6c7b6', '#FFA07A','#FA8072','#E9967A','#F08080','#CD5C5C','#DC143C','#B22222','#FF0000','#8B0000','#800000','#FF6347','#FF4500','#DB7093']);
            $Combo->getOptions()->setBackgroundColor('#F2F2F2');

            $series = new \CMEN\GoogleChartsBundle\GoogleCharts\Options\ComboChart\Series();
            $series->setType('line');
            $Combo->getOptions()->setSeries([(int)$tgrupos => $series]);


            $inventario = $conn->query("SELECT
    Count(vault.`code`) AS inventario
    FROM
    vault
    WHERE
    vault.assigned IS NULL AND
    vault.company_id = ".$companyId)->fetchAll();

            $inv_total = (int)$inventario[0]['inventario'];

            $gauge = new GaugeChart();
            $gauge->getOptions()->setWidth(350);
            $gauge->getOptions()->setHeight(250);
            $gauge->getOptions()->setRedFrom(0);
            $gauge->getOptions()->setRedTo(3000);
            $gauge->getOptions()->setYellowFrom(3000);
            $gauge->getOptions()->setYellowTo(9000);
            $gauge->getOptions()->setGreenFrom(9000);
            $gauge->getOptions()->setGreenTo(30000);
            $gauge->getOptions()->setMax(30000);
            $gauge->getData()->setArrayToDataTable([
                ['Label', 'Value'],
                ['Inventario', (int)$inv_total]
            ]);

            return $this->render('reports/customerReport.html.twig', array('logo' => $logo, 'piechart' => $Combo, 'gaugechart' => $gauge));
        }
    }
    
    /**
     * @Route("/customer/detailed", name="customer_detailed")
     */
    public function customerDetailed(Request $request){
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
                $conn = $em->getConnection();
                $error=null;
        //        $companyId=7;
                $query="SELECT vault_group.`name` AS grupo_inventario,
                    YEAR(vault.assigned) AS anio_asignacion,
        MONTHNAME(vault.assigned) AS mes_asignacion,
        vault.assigned AS `fecha_asignacion`,
        CONCAT('*********',RIGHT(vault.`code`,4)) AS bono,
        vault.id AS bono_id,
        vault.code_value AS valor_bono,
        members.member_name AS nombre,
        members.member_email AS email,
        members.id AS id,
        members.mobile_phone AS celular
        FROM
        vault
        INNER JOIN vault_group ON vault.vault_group_id = vault_group.id
        INNER JOIN members ON vault.member_id = members.id
        WHERE
        vault.assigned IS NOT NULL AND
        vault.company_id = ".$companyId;

                $report = $conn->query($query)->fetchAll();

                if(empty($report)){
                    $error = 'No Hay Datos para Mostrar';
                }
                return $this->render('reports/customerDetailed.html.twig', array('logo' => $logo, 'data' => $report, 'error' => $error));

        }
    }
}
