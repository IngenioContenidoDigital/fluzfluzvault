<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use AppBundle\Entity\Company;

class MemberGroupController extends Controller{
    /** @Route("/membergroup/remove")*/
    public function removeGroup(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $error = NULL;
            $id_group = $request->query->get('gp');

            $em = $this->getDoctrine()->getManager();
            $member_group = $em->find('AppBundle\Entity\MemberGroup', $id_group);
            if(strlen($member_group->getName())>0){
                $em->remove($member_group);
                $em->flush();
                return new Response($member_group->getName());
            }else{
                throw new NotFoundHttpException("Grupo no Encontrado");
            }
        }
    }
    
    /** @Route("/membergroup/edit")*/
    public function editMemberGroup(Request $request){
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $logo='logo-2.png';
            return $this->render('security/login.html.twig', array(
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
            'error' => null, 'last_username'=>null,'logo'=>$logo));
        }else{
            $error = NULL;

            $id_group = $request->query->get('gp');
            $id_company = $request->query->get('cp');
            $em = $this->getDoctrine()->getManager();
            $member_group = $em->find('AppBundle\Entity\MemberGroup', $id_group);
            $company = $em->find('AppBundle\Entity\Company', $id_company);

            $form = $this->createFormBuilder()
                ->setMethod('POST')
                ->setAttribute('id', 'group-form')
                ->add('name', TextType::class, ['data' => $member_group->getName()])
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // $form->getData() holds the submitted values
                try{
                    $member_group->setName($form['name']->getData());

                    $this->getDoctrine()->getManager()->persist($member_group);
                    $this->getDoctrine()->getManager()->flush();
                }catch(Exception $e){
                    $error = isset($error) ? $e->getMessage() : $error;
                }
                $groups = $em->getRepository(Company::class)->findCompanyGroups($company);
                return $this->render('admin/company/companyListGroups.html.twig', array('error'  => $error, 'company' => $company, 'groups' => $groups));
            }else{
                return $this->render('admin/member_group/groupEdit.html.twig', array('error' => $error, 'form' => $form->createView()));
            }
        }
    }
}
