<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use League\Csv\Reader;
use AppBundle\Entity\Member;
use AppBundle\Entity\Vault;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,));
        }else{
            $form = $this->createFormBuilder()
                    ->setAttribute('id', 'myform')
                    ->setAction($this->generateUrl('homepage'))
                    ->setMethod('POST')
                    ->add('file', FileType::class,array(
                "attr" =>array("class" => "custom-file-input", "id"=>"file", "required"=>true, 'accept' => ".csv")))
                ;
            $form = $form->getForm();
            $form->handleRequest($request);
             if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                
                $file=$form['file']->getData();
                $ext=$file->guessExtension();
                $file_name=time().".".$ext;
                $file->move("uploads", $file_name);
                
                $reader = Reader::createFromPath($this->get('kernel')->getRootDir().'/../web/uploads/'.$file_name)
                ->setHeaderOffset(0)
                ;
                foreach ($reader as $row) {
                    $member = $this->getDoctrine()->getRepository('AppBundle:Member')
                        ->findMemberByEmail($row['member_email']);
                    $total=count($member);

                    if ($total == 0) {
                        $member = (new Member())
                            ->setMemberName($row['member_name'])
                            ->setMemberEmail($row['member_email'])
                            ->setMobilePhone($row['mobile_phone'])
                            ->setIdentification($row['identification'])
                            ->setDateAdd(new \DateTime())
                        ;
                        $this->getDoctrine()->getManager()->persist($member);
                   }
                }

                // save / write the changes to the database
                $this->getDoctrine()->getManager()->flush();

                $results = $this->getDoctrine()->getRepository('AppBundle:Member')
                        ->findAllMembers();
                $total = count($results);
                $bonos = $this->getDoctrine()->getRepository('AppBundle:Vault')
                        ->findCodeValues();

                return $this->render('member/listmembers.html.twig',array('members' => $results,
                    'total'=> $total, 'bonos'=>$bonos));
                
                
                

                // ... perform some action, such as saving the task to the database
                // for example, if Task is a Doctrine entity, save it!
                // $em = $this->getDoctrine()->getManager();
                // $em->persist($task);
                // $em->flush();

                //return $this->redirectToRoute('listarmiembros');
            }

            
            return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'form' => $form->createView()
            ]);
        }
        
        
    }
    
}
