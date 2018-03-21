<?php
/**
 * Created by PhpStorm.
 * User: G_STY
 * Date: 31/12/2017
 * Time: 13:08
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Art;
use AppBundle\Entity\ArtForm;
use AppBundle\Entity\Category;
use AppBundle\Form\ArtType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ArtController extends Controller
{
    /**
     * @Route("dashboard/art", name="dashboard_art")
     */
    public function artAction(){
        $rp = $this->getDoctrine()->getRepository(Art::class);
        $arts = $rp->findAll();

        $data = array();
        /** @var Art $art */
        foreach ($arts as $art){
            $array = array();
            $array['libelle'] = $art->getLibelle();
            $artforms = array();
            /** @var ArtForm $artform */
            foreach ($art->getArtForms() as $artform){
                array_push($artforms,$artform->getLibelle());
            }
            $array['art_forms'] = $artforms;
            $array['createAt'] = date_format($art->getCreateAt(),'d/m/Y H:i');
            $array['updateAt'] = $art->getUpdateAt() ? date_format($art->getUpdateAt(),'d/m/Y H:i') : " - ";
            $array['actions'] = $this->renderView('back/art/fragments/buttons_actions_art.html.twig',array('id'=>$art->getId()));

            array_push($data,$array);
        }
        return $this->render('back/art/index.html.twig',array('data'=>json_encode($data)));
    }

    /**
     * @Route("dashboard/art/create", name="dashboard_art_create")
     */
    public function artCreateAction(Request $request){
        $art = new Art();
        $form = $this->createForm(ArtType::class,$art);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $art->setCreateAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($art);
            $em->flush();

            return $this->redirectToRoute('dashboard_art');
        }

        return $this->render('back/art/create_art.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("dashboard/art/update/{id}", name="dashboard_art_update")
     */
    public function artUpdateAction(Request $request, Art $art){
        $form = $this->createForm(ArtType::class,$art);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $art->setUpdateAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('dashboard_art');
        }
        return $this->render('back/art/edit_art.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("dashboard/art/delete/{id}", name="dashboard_art_delete")
     */
    public function artDeleteAction(Request $request, Art $art){
        $em = $this->getDoctrine()->getManager();
        $em->remove($art);
        $em->flush();

        return $this->redirectToRoute('dashboard_art');
    }
}