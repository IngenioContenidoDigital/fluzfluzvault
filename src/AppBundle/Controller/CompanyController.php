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
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Company;
use AppBundle\Entity\Member;
use AppBundle\Entity\Customer;
use AppBundle\Entity\Vault;


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
            ->add('logo', FileType::class)
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
}
