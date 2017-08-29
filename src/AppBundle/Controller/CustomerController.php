<?php

// src/AppBundle/Controller/CustomerController.php
namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Customer;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class CustomerController extends Controller{
    /** @Route("/customer/create")*/
    public function createCustomer(){
        
        $em = $this->getDoctrine()->getManager();

        $customer = new Customer();
        $customer->setName('Bodytech');
        $customer->setPassword('FluzFluz2017*');
        $customer->setCompany('Bodytech Colombia');

        // tells Doctrine you want to (eventually) save the Product (no queries yet)
        $em->persist($customer);

        // actually executes the queries (i.e. the INSERT query)
        $em->flush();

        return new Response('Se ha registrado un nuevo cliente con el Id '.$customer->getId());
    }

    // if you have multiple entity managers, use the registry to fetch them
    /** @Route("/customer/view")*/
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
        
        $customer->setName('Bodytech');
        $encoder = $this->get('security.encoder_factory')->getEncoder($customer);
        $encodedPassword = $encoder->encodePassword('FluzFluz2017**', $customer);
        $customer->setPassword($encodedPassword);
        $em->flush();
        return new Response("<div>Se ha realizado la edición del cliente solicitado. Su nuevo nombre es: </div>".$customer->getName());
    }
    
}