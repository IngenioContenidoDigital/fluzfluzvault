<?php

// src/AppBundle/Controller/SecurityController.php

namespace AppBundle\Controller;

use AppBundle\Entity\Customer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends Controller{
    /** @Route("/login", name="login")*/
    
    public function loginAction(Request $request, AuthenticationUtils $authUtils){
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }
    
    private function registerAction(UserPasswordEncoderInterface $encoder, $password = null){
        $username = new Customer();
        $encoded = $encoder->encodePassword($username, $password);
        $username->setPassword($encoded);
    }
}

