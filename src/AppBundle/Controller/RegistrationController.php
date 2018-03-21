<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Controller managing the registration.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class RegistrationController extends BaseController
{

    private $eventDispatcher;
    private $formFactory;
    private $userManager;
    private $tokenStorage;

    public function __construct(EventDispatcherInterface $eventDispatcher, FactoryInterface $formFactory, UserManagerInterface $userManager, TokenStorageInterface $tokenStorage)
    {
        parent::__construct($eventDispatcher, $formFactory, $userManager, $tokenStorage);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $this->userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    /*$url = $this->generateUrl('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);*/
                    $response = new JsonResponse(array('status' => 1, 'url' => $this->generateUrl('fos_user_registration_confirmed')));
                }

                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        /*return $this->render('@FOSUser/Registration/register.html.twig', array(
            'form' => $form->createView(),
        ));*/
        if($request->getMethod() !== 'POST')
        {
            return $this->render('@FOSUser/Registration/register.html.twig', array(
                'form' => $form->createView(),
            ));
        }
        else
        {
            $errors = array();
            foreach($form->getErrors() as $error)
            {
                $errors[] = $error->getMessageTemplate();
            }

            if($form->get('plainPassword')->getData() === null)
            {
                $errors[] = 'password.mismatch';
            }

            return new JsonResponse(array('status' => 0, 'errors' => $errors));
        }
    }

    /**
     * Tell the user his account is now confirmed.
     */
    public function confirmedAction()
    {
        return $this->redirectToRoute('home');
        /*$user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('@FOSUser/Registration/confirmed.html.twig', array(
            'user' => $user,
            'targetUrl' => $this->getTargetUrlFromSession(),
        ));*/
    }
}
