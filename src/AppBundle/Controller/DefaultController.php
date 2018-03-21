<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Art;
use AppBundle\Entity\Artwork;
use AppBundle\Entity\Event;
use AppBundle\Entity\News;
use AppBundle\Entity\Presentation;
use AppBundle\Form\ArtworkType;
use AppBundle\Form\EventType;
use AppBundle\Form\NewsType;
use AppBundle\Form\PresentationType;
use AppBundle\Form\UserType;
use Doctrine\ORM\Events;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FFMpeg\FFMpeg;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        $repository_artwork = $this->getDoctrine()->getRepository(Artwork::class);
        $repository_presentation = $this->getDoctrine()->getRepository(Presentation::class);
        $repository_news = $this->getDoctrine()->getRepository(News::class);
        $repository_event = $this->getDoctrine()->getRepository(Event::class);
        $artworks = $repository_artwork->findAll();
        $presentation = $repository_presentation->find(1);
        $news = $repository_news->findBy(array(),array('createAt'=>'DESC'),5,null);
        $events = $repository_event->findAll();

        return $this->render('base.html.twig',array('artworks'=>$artworks,'presentation'=>$presentation,'news'=>$news,'events'=>$events));
    }


    /**
     * @Route("/profil", name="profil")
     */
    public function  profilAction(Request $request){
        $user = $this->getUser();

        $profil_img = $user->getImageName();

        $form = null;
        /*chmod($this->getParameter('profil_gif_directory'),0777);*/

        /*$ffmpeg_binaries = $this->getParameter('binary_ffmpeg');
        $ffmprobe_binaries = $this->getParameter('binary_ffmprobe');
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => 'C:/FFmpeg/bin/ffmpeg.exe',
            'ffprobe.binaries' => 'C:/FFmpeg/bin/ffprobe.exe'
        ]);

        $video = $ffmpeg->open($this->getParameter('profil_video_directory').'/demo_kt.mp4');
        $video->gif(\FFMpeg\Coordinate\TimeCode::fromSeconds(2),new \FFMpeg\Coordinate\Dimension(640, 480), 3)
            ->save($this->getParameter('profil_gif_directory'));*/

        if($profil_img) {
            $user->setImageName(
                new File($this->getParameter('profil_img_directory').'/'.$profil_img)
            );
            $form = $this->createForm(UserType::class,$user,['validation_groups'=>'edit']);
        }
        else{
            $form = $this->createForm(UserType::class,$user,['validation_groups'=>'create']);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($user->getImageName()){
                $file = $user->getImageName();

                $fileName = md5(uniqid()).'.'.$file->guessExtension();

                $file->move(
                    $this->getParameter('profil_img_directory'),
                    $fileName
                );

                $user->setImageName($fileName);
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                $imagemanagerResponse = $this->container->get('liip_imagine.controller');
                $imagemanagerResponse->filterAction($request, $this->getParameter('profil_img_directory')."/".$fileName, 'my_thumb');

            }else{
                $user->setImageName($profil_img);
                $em = $this->getDoctrine()->getManager();
                $em->flush();
            }

            return $this->redirect($this->generateUrl('profil'));
        }

        return $this->render('profil.html.twig',array('profil_img'=>$profil_img,'form'=>$form->createView()));
    }

    /**
     * @Route("/artwork",name="artwork")
     */
    public function artworkAction(Request $request){

        $artwork = new Artwork();
        $form = null;

        $form = $this->createForm(ArtworkType::class,$artwork,['validation_groups'=>'create']);


        $form->handleRequest($request);

        if($request->isXmlHttpRequest()){
            $id = $request->get('id');
            $array = array();
            if(!$id){
                $array = array('id'=>null,'name'=>'Formes d\'art');
            }
            /** @var Art $art */
            $art = $this->getDoctrine()
                ->getManager()
                ->getRepository('AppBundle:Art')
                ->find($id);
            $artforms = $art->getArtForms();


            for($i=0;$i<count($artforms);$i++){
                $artform = array();
                $artform['id'] = $artforms[$i]->getId();
                $artform['name'] = $artforms[$i]->getLibelle();
                array_push($array,$artform);
            }

            return new JsonResponse(array('artforms'=>$array));
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $artwork->getMedia();

            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('artwork_media_directory'),
                $fileName
            );
            $artwork->setMedia($fileName);
            $artwork->setCreateAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($artwork);
            $em->flush();

            /*$imagemanagerResponse = $this->container->get('liip_imagine.controller');
            $imagemanagerResponse->filterAction($request, $this->getParameter('profil_img_directory')."/".$fileName, 'my_thumb');*/

            return $this->redirect($this->generateUrl('home'));
        }
        return $this->render('artwork.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashBoardAction(Request $request){
        return $this->render('back/dashboard.html.twig');
    }

    /**
     * @Route("/dashboard/edit_presentation", name="edit_presentation")
     */
    public function editPresentationAction(Request $request){

        $repository_presentation = $this->getDoctrine()->getRepository(Presentation::class);

        $presentation = $repository_presentation->find(1);
        $form =$this->createForm(PresentationType::class,$presentation);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($presentation);
            $em->flush();
        }
        return $this->render('back/edit_presentation.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("/dashboard/news", name="dashboard_news")
     */
    public function dashboardNewsAction(Request $request){
        $rp = $this->getDoctrine()->getRepository(News::class);
        $news = $rp->findAll();

        $data = array();
        foreach ($news as $new){
            $array = array();
            $array['title'] = $new->getTitle();
            $array['content'] = $new->getContent();
            $array['createAt'] = date_format($new->getCreateAt(),'d/m/Y H:i');
            $array['updateAt'] = $new->getUpdateAt() ? date_format($new->getUpdateAt(),'d/m/Y H:i') : " - ";
            $array['actions'] = $this->renderView('back/news/fragments/buttons_news.html.twig',array('id'=>$new->getId()));

            array_push($data,$array);
        }

        return $this->render('back/news/index.html.twig',array('data'=>json_encode($data)));
    }

    /**
     * @Route("/dashboard/create_news", name="create_news")
     */
    public function createNewsAction(Request $request){

        $news = new News();
        $form = $this->createForm(NewsType::class,$news);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $news->getMedia();

            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('evts_img_directory'),
                $fileName
            );

            $news->setMedia($fileName);
            $news->setCreateAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($news);
            $em->flush();

            return $this->redirectToRoute('dashboard_news');
        }

        return $this->render('back/news/create_news.html.twig',array('form'=>$form->createView()));

    }

    /**
     * @Route("/dashboard/edit_news/{id}", name="edit_news")
     */
    public function editNewsAction(Request $request, News $news){
        $img = $news->getMedia();
        $news->setMedia(
            new File($this->getParameter('evts_img_directory').'/'.$img)
        );
        $form = $this->createForm(NewsType::class,$news);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $news->getMedia();

            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('evts_img_directory'),
                $fileName
            );

            $news->setMedia($fileName);
            $news->setUpdateAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('dashboard_news');
        }
        return $this->render('back/news/edit_news.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("/dashboard/delete_news/{id}", name="delete_news")
     */
    public function  deleteNewsAction(Request $request, News $news){
        $em = $this->getDoctrine()->getManager();
        $em->remove($news);
        $em->flush();

        return $this->redirectToRoute('dashboard_news');
    }

    /**
     * @Route("/dashboard/events", name="dashboard_events")
     */
    public function dashboardEventsAction(Request $request){
        $rp = $this->getDoctrine()->getRepository(Event::class);
        $events = $rp->findAll();

        $data = array();
        foreach ($events as $event){
            $array = array();
            $array['title'] = $event->getTitle();
            $array['description'] = $event->getDescription();
            $array['createAt'] = date_format($event->getCreateAt(),'d/m/Y H:i');
            $array['updateAt'] = $event->getUpdateAt() ? date_format($event->getUpdateAt(),'d/m/Y H:i') : " - ";
            $array['actions'] = $this->renderView('back/events/fragments/buttons_event.html.twig',array('id'=>$event->getId()));

            array_push($data,$array);
        }

        return $this->render('back/events/index.html.twig',array('data'=>json_encode($data)));
    }

    /**
     * @Route("/dashboard/create_event", name="create_event")
     */
    public function createEventAction(Request $request){

        $event = new Event();
        $form = $this->createForm(EventType::class,$event);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $file = $event->getMedia();

            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('evts_img_directory'),
                $fileName
            );

            $event->setMedia($fileName);

            $event->setCreateAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute('dashboard_events');
        }

        return $this->render('back/events/create_event.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("/dashboard/edit_event/{id}", name="edit_event")
     */
    public function editEventAction(Request $request, Event $event){
        $img = $event->getMedia();
        $event->setMedia(
            new File($this->getParameter('evts_img_directory').'/'.$img)
        );
        $form = $this->createForm(EventType::class,$event,['validation_groups'=>'edit']);
        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){

            $file = $event->getMedia();

            $fileName = md5(uniqid()).'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('evts_img_directory'),
                $fileName
            );

            $event->setMedia($fileName);
            $event->setUpdateAt(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirectToRoute('dashboard_events');
        }
        return $this->render('back/events/edit_event.html.twig',array('form'=>$form->createView(),'img'=>$img));
    }

    /**
     * @Route("/dashboard/delete_event/{id}", name="delete_event")
     */
    public function  deleteEventAction(Request $request, Event $event){
        $em = $this->getDoctrine()->getManager();
        $em->remove($event);
        $em->flush();

        return $this->redirectToRoute('dashboard_events');
    }
}
