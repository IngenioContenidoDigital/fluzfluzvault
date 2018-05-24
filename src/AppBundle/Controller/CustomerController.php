<?php

// src/AppBundle/Controller/CustomerController.php
namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Company;
use AppBundle\Entity\Member;
use AppBundle\Entity\Customer;
use AppBundle\Entity\Vault;


class CustomerController extends Controller{
    /** @Route("/customer/create")*/
    public function createCustomer(UserPasswordEncoderInterface $encoder, Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $error = NULL;

            $list_companies = $this->getDoctrine()->getRepository('AppBundle:Company')
                ->listCompanies();

            $opciones = array();
            foreach($list_companies as $company){
                $opciones = array_merge($opciones,array($company['name'] => $company['id']));
            }

            $form = $this->createFormBuilder()
                ->setMethod('POST')
                ->setAction('/customer/create')
                ->setAttribute('id', 'user-form')
                ->add('name', TextType::class)
                ->add('email', EmailType::class)
                ->add('company', ChoiceType::class, array(
                    'choices' => $opciones,
                    'placeholder' => ' -- Elija una Empresa --',
                    'required' => true
                ))
                ->add('password', PasswordType::class)
                ->add('is_active', CheckboxType::class, array('required' => false, 'value' => '1', 'attr' => array('checked'=>true)))
                ->add('role_user', CheckboxType::class, array('required' => false, 'value' => 'ROLE_USER', 'attr' => array('checked'=>true)))
                ->add('role_admin', CheckboxType::class, array('required' => false, 'value' => 'ROLE_ADMIN'))
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                try{
                    $em = $this->getDoctrine()->getManager();

                    $customer = new Customer();
                    $customer->setName($form['name']->getData());

                    $company = $em->find('AppBundle\Entity\Company', $form['company']->getData());

                    $customer->setCompany($company);
                    $customer->setEmail($form['email']->getData());
                    $plainPassword = $form['password']->getData();
                    $encoder = $this->get('security.encoder_factory')->getEncoder($customer);
                    $encoded = $encoder->encodePassword($plainPassword, $customer);
                    $customer->setPassword($encoded);
                    $customer->setIsActive($form['is_active']->getData());
                    if($form['role_user']->getData()){
                        $customer->setRoles('ROLE_USER');
                    }
                    if($form['role_admin']->getData()){
                        $customer->setRoles('ROLE_ADMIN');
                    }

                    // tells Doctrine you want to (eventually) save the Product (no queries yet)
                    $em->persist($customer);

                    // actually executes the queries (i.e. the INSERT query)
                    $em->flush();
                }catch(Exception $e){
                    $error = isset($e) ? $e->getMessage() : $error;
                }
                return $this->forward('AppBundle\Controller\CustomerController::getCustomers', array('error'  => $error));
            }
                return $this->render('admin/createCustomer.html.twig',array('error'=>$error, 'form'=>$form->createView()));
        }
    }

    // if you have multiple entity managers, use the registry to fetch them
    /** @Route("/customer/view")*/
    public function getCustomers(){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            try{
                $error = NULL;
                $customer_list = $this->getDoctrine()
                    ->getRepository(Customer::class)
                    ->findAll();
                /*print_r($customer_list);
                die();*/
            }catch(Exception $e){
                $error = isset($e) ? $e->getMessage() : $error;
            }
            return $this->render('admin/viewCustomer.html.twig',array('error' => $error, 'customers' => $customer_list));
        }
    }
    
    public function getCustomer($idcustomer = null){
        $idcustomer=1;
        $customer = $this->getDoctrine()
                ->getRepository(Customer::class)
                ->find($idcustomer);
        if(!$customer){
            throw $this->createNotFoundException("No se ha encontrado el cliente con el id: ".$idcustomer);
        }
        //return new Response('<div>El cliente solicitado se llama: </div>'.$customer->name);
        return $this->render('customer/viewCustomer.html.twig',array('customer' => $customer));
    }
    
    /** @Route("/customer/edit")*/
    public function editCustomer(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $error = NULL;

            $id_customer = $request->query->get('cs');
            $id_company = $request->query->get('cp');
            $em = $this->getDoctrine()->getManager();
            $customer = $em->find('AppBundle\Entity\Customer', $id_customer);
            $company = $em->find('AppBundle\Entity\Company', $id_company);

            $list_companies = $this->getDoctrine()->getRepository('AppBundle:Company')
                ->listCompanies();

            $opciones = array();
            foreach($list_companies as $c){
                $opciones = array_merge($opciones,array($c['name'] => $c['id']));
            }

            $form = $this->createFormBuilder()
                ->setMethod('POST')
                ->add('cs', HiddenType::class, ['data' => $customer->getIdCustomer()])
                ->add('cp', HiddenType::class, ['data' => $company->getId()])
                ->add('name', TextType::class, ['data' => $customer->getName()])
                ->add('email', EmailType::class, ['data' => $customer->getEmail()])
                ->add('company', ChoiceType::class, array(
                    'choices' => $opciones,
                    'placeholder' => ' -- Elija una Empresa --',
                    'required' => true,
                    'data' => $company->getId()
                ))
                ->add('password', PasswordType::class, array('required' => false))
                ->add('is_active', CheckboxType::class, array('required' => false, 'value' => '1', 'attr' => array('checked'=>$customer->getIsActive())))
                ->add('role_user', CheckboxType::class, array('required' => false, 'value' => 'ROLE_USER', 'attr' => array('checked'=>  in_array('ROLE_USER', $customer->getRoles()))))
                ->add('role_admin', CheckboxType::class, array('required' => false, 'value' => 'ROLE_ADMIN', 'attr' => array('checked' => in_array('ROLE_ADMIN', $customer->getRoles()))))
                ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                try{
                    $em = $this->getDoctrine()->getManager();

                    $customer->setName($form['name']->getData());

                    $company1 = $em->find('AppBundle\Entity\Company', $form['company']->getData());

                    $customer->setCompany($company1);
                    $customer->setEmail($form['email']->getData());
                    if($form['password']->getData()!=NULL){
                        $plainPassword = $form['password']->getData();
                        $encoder = $this->get('security.encoder_factory')->getEncoder($customer);
                        $encoded = $encoder->encodePassword($plainPassword, $customer);
                        $customer->setPassword($encoded);
                    }
                    $customer->setIsActive($form['is_active']->getData());
                    if($form['role_user']->getData()){
                        $customer->setRoles('ROLE_USER');
                    }else{
                        $customer->removeRole('ROLE_USER');
                    }
                    if($form['role_admin']->getData()){
                        $customer->setRoles('ROLE_ADMIN');
                    }else{
                        $customer->removeRole('ROLE_ADMIN');
                    }

                    // tells Doctrine you want to (eventually) save the Product (no queries yet)
                    $em->persist($customer);

                    // actually executes the queries (i.e. the INSERT query)
                    $em->flush();
                }catch(Exception $e){
                    $error = isset($e) ? $e->getMessage() : $error;
                }
                return $this->forward('AppBundle\Controller\CustomerController::getCustomers', array('error'  => $error));

            }
            return $this->render('admin/customer/editCustomer.html.twig',array('error'=>$error, 'form'=>$form->createView()));
        }
    }
    
}
