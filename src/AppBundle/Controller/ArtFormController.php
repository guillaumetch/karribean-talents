<?php
/**
 * Created by PhpStorm.
 * User: G_STY
 * Date: 04/01/2018
 * Time: 19:33
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Art;
use AppBundle\Entity\ArtForm;
use AppBundle\Form\ArtFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ArtFormController extends Controller
{
    /**
     * @Route("dashboard/art_form", name="dashboard_art_form")
     */
    public function artFormAction(){
        $rp = $this->getDoctrine()->getRepository(ArtForm::class);
        $art_forms = $rp->findAll();

        $data = array();
        foreach ($art_forms as $art_form){
            $array = array();
            $array['libelle'] = $art_form->getLibelle();
            $array['art'] = $art_form->getArt()->getLibelle();
            $array['createAt'] = date_format($art_form->getCreateAt(),'d/m/Y H:i');
            $array['updateAt'] = $art_form->getUpdateAt() ? date_format($art_form->getUpdateAt(),'d/m/Y H:i') : " - ";
            $array['actions'] = $this->renderView('back/art_form/fragments/buttons_actions_art_form.html.twig',array('id'=>$art_form->getId()));

            array_push($data,$array);
        }
        return $this->render('back/art_form/index.html.twig',array('data'=>json_encode($data)));
    }

    /**
     * @Route("dashboard/art_form/create", name="dashboard_art_form_create")
     */
    public function artFormCreateAction(Request $request){
        $art_form = new ArtForm();
        $form = $this->createForm(ArtFormType::class,$art_form);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $art_id = $art_form->getArt();
            $rp = $this->getDoctrine()->getRepository(Art::class);
            /** @var Art $art */
            $art = $rp->find($art_id);
            $art_form->setCreateAt(new \DateTime());
            $art->addArtForm($art_form);
            $em = $this->getDoctrine()->getManager();
            $em->persist($art_form);
            $em->persist($art);
            $em->flush();

            return $this->redirectToRoute('dashboard_art_form');
        }

        return $this->render('back/art_form/create_art_form.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("dashboard/art_form/update/{id}", name="dashboard_art_form_update")
     */
    public function artFormUpdateAction(Request $request, ArtForm $art_form){

        $form = $this->createForm(ArtFormType::class,$art_form);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $art_form->setUpdateAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('dashboard_art_form');
        }
        return $this->render('back/art_form/edit_art_form.html.twig',array('form'=>$form->createView()));

    }

    /**
     * @Route("dashboard/art_form/delete/{id}", name="dashboard_art_form_delete")
     */
    public function artFormDeleteAction(Request $request, ArtForm $art_form){
        $em = $this->getDoctrine()->getManager();
        $em->remove($art_form);
        $em->flush();

        return $this->redirectToRoute('dashboard_art_form');
    }
}