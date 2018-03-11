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
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\Company;
use AppBundle\Entity\Member;
use AppBundle\Entity\Customer;
use AppBundle\Entity\Vault;


class CustomerController extends Controller{
    /** @Route("/customer/create")*/
    public function createCustomer(UserPasswordEncoderInterface $encoder, Request $request){
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

    // if you have multiple entity managers, use the registry to fetch them
    /** @Route("/customer/view")*/
    public function getCustomers(){
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
    public function editCustomer($idcustomer = null){
        $idcustomer = 1;
        $em = $this->getDoctrine()->getManager();
        $customer = $em->getRepository(Customer::class)->find($idcustomer);
        if(!$customer){
            throw $this->createNotFoundException("No se ha localizado el cliente: ".$idcustomer." que intenta modificar");
        }
        
        //$customer->setName('Bodytech');
        $encoder = $this->get('security.encoder_factory')->getEncoder($customer);
        $encodedPassword = $encoder->encodePassword('1031143285', $customer);
        $customer->setPassword($encodedPassword);
        $em->flush();
        return new Response("<div>Se ha realizado la edición del cliente solicitado. Su nombre es: </div>".$customer->getName());
    }
    
}
