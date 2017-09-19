<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Vault;
use AppBundle\Entity\Member;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class VaultController extends Controller{
    /** @Route("/vault/assign")*/
    public function assignToMember(Request $request, \Swift_Mailer $mailer){
         if ($request->isMethod('POST')) {
             $total_asignados=0;
             $valorbonos=0;
            $data =$request->request->all();
            foreach($data as $k /*member*/ => $d /*value*/){
                $vault = $this->getDoctrine()->getRepository(Vault::class)
                            ->findFirstAvailableCodeByValue($data['tipo-bono']);
                if(count($vault)>0 && $k!='tipo-bono'){
                    $member = $this->getDoctrine()->getRepository(Member::class)
                            ->find($k);
                    $id = $member->getIdMember();
                    $email = $member->getMemberEmail();
                    $name = $member->getMemberName();
                    
                    $vault->AssignCode($id);
                    $total_asignados+=1;
                    $bono = $vault->getCode();
                    $fecha = $vault->getExpiration();
                    $valor = $vault->getCodeValue();
                    $valorbonos+=$valor;
                    $cantidad = 1;
                    
                    $this->getDoctrine()->getManager()->flush();
                    
                    
                    
                $message = (new \Swift_Message('Bono de Bienvenida Credencial – Bodytech'))
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
                    
                    $mailer->send($message);
                }
            }
            return $this->render('vault/results.html.twig',array('bonosasignados'=>$total_asignados, 'valortotal'=>$valorbonos));
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
}
