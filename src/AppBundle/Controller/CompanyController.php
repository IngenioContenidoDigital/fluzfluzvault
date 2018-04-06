<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Company;
use AppBundle\Entity\Member;
use AppBundle\Entity\Customer;
use AppBundle\Entity\Vault;
use AppBundle\Entity\CompanyEmail;


class CompanyController extends Controller{
    /** @Route("/company/create")*/
    public function createCompany(Request $request){
        $error = NULL;
        
        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->setAction('/company/create')
            ->setAttribute('id', 'company-form')
            ->add('name', TextType::class)
            ->add('nit', TextType::class)
            ->add('email', EmailType::class)
            ->add('logo', FileType::class, array('attr' => array("required"=>true)))
            ->add('phone', NumberType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            try{
                $file=$form['logo']->getData();
                $ext=$file->guessExtension();
                $file_name=time().".".$ext;
                $file->move("images/company/", $file_name);

                $company = (new Company())
                    ->setName($form['name']->getData())
                    ->setEmail($form['email']->getData())  
                    ->setNit($form['nit']->getData())
                    ->setLogo($file_name)
                    ->setPhone($form['phone']->getData())
                ;
                $this->getDoctrine()->getManager()->persist($company);
                $this->getDoctrine()->getManager()->flush();
            }catch(Exception $e){
                $error = isset($error) ? $e->getMessage() : $error;
            }
            return $this->forward('AppBundle\Controller\CompanyController::listCompanies', array('error'  => $error));
            //return $this->render('admin/company/companyCreate.html.twig', array('error' => $error, 'form' => $form->createView()));
        }else{
            return $this->render('admin/company/companyCreate.html.twig', array('error' => $error, 'form' => $form->createView()));
        }
    }
    
    /**
     * @Route("/company")
     */
    public function listCompanies($error= NULL){
        $results = $this->getDoctrine()->getRepository('AppBundle:Company')
                ->findAll();
        
        return $this->render('admin/company/companyList.html.twig',array('error' => $error, 'companies' => $results));
    }
    
    /**
     * @Route("/company/email")
     */
    public function createEmail(Request $request){
        $error = NULL;

        $id_company = $request->query->get('cp');
        $em = $this->getDoctrine()->getManager();
        $company = $em->find('AppBundle\Entity\Company', $id_company);
        
        $res = $company->getTemplates();
        if(count($res)>0){
            $name=$res[0]->getName();
            $tpl=$res[0]->getTemplate();        
        }else{
            $name="";
            $tpl="";        
        }
        
        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->setAttribute('id', 'company-template')
            ->add('company', HiddenType::class, array('data' => $company->getId()))
            ->add('name', TextType::class, array('data' => $name))
            ->add('template', CKEditorType::class, array('data' => $tpl))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $template=$em->getRepository(CompanyEmail::class)
                    ->findTemplateByCompany($company);
            if($template==NULL) $template = new CompanyEmail();
            $template->setName($form['name']->getData());
            $template->setTemplate($form['template']->getData());
            
            $company1 = $em->find('AppBundle\Entity\Company', $form['company']->getData());
            $template->setCompany($company1);
            
            $this->getDoctrine()->getManager()->persist($template);
            $this->getDoctrine()->getManager()->flush();
            
            $results = $this->getDoctrine()->getRepository('AppBundle:Company')
                ->findAll();
            $error="La plantilla de Email ha sido creada de forma exitosa";
            
            return $this->render('admin/company/companyList.html.twig', array('error' => $error, 'companies' => $results));
        }
        return $this->render('admin/company/companyTemplate.html.twig', array('error' => $error, 'company' => $company->getName(), 'form' => $form->createView()));
    }
    
    /** @Route("/company/edit")*/
    public function editCompany(Request $request){
        $error = NULL;
        
        $id_company = $request->query->get('cp');
        $em = $this->getDoctrine()->getManager();
        $company = $em->find('AppBundle\Entity\Company', $id_company);
        
        $form = $this->createFormBuilder()
            ->setMethod('POST')
            ->setAttribute('id', 'company-form')
            ->add('name', TextType::class, ['data' => $company->getName()])
            ->add('nit', TextType::class, ['data' => $company->getNit()])
            ->add('email', EmailType::class, ['data' => $company->getEmail()])
            ->add('logo', FileType::class, array('required' => false))
            ->add('phone', NumberType::class, ['data' => $company->getPhone()]) 
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            try{
                
                $company->setName($form['name']->getData());
                $company->setEmail($form['email']->getData());  
                $company->setNit($form['nit']->getData());
                $company->setPhone($form['phone']->getData());
                if(!empty($form['logo']->getData())){
                    $file=$form['logo']->getData();
                    $ext=$file->guessExtension();
                    $file_name=time().".".$ext;
                    $file->move("images/company/", $file_name);   
                    $company->setLogo($file_name);
                }

                $this->getDoctrine()->getManager()->persist($company);
                $this->getDoctrine()->getManager()->flush();
            }catch(Exception $e){
                $error = isset($error) ? $e->getMessage() : $error;
            }
            return $this->forward('AppBundle\Controller\CompanyController::listCompanies', array('error'  => $error));
            //return $this->render('admin/company/companyCreate.html.twig', array('error' => $error, 'form' => $form->createView()));
        }else{
            return $this->render('admin/company/companyEdit.html.twig', array('error' => $error, 'form' => $form->createView()));
        }
    }
    
    /**
     * @Route("/company/group")
     */
    public function CompanyGroups(Request $request){
        $error = NULL;
        $id_company = $request->query->get('cp');
        $em = $this->getDoctrine()->getManager();
        $company = $em->find('AppBundle\Entity\Company', $id_company);
        $groups = $em->getRepository(Company::class)->findCompanyGroups($company);
        return $this->render('admin/company/companyListGroups.html.twig', array('error' => $error, 'company' => $company,'groups' => $groups));
    }
}
