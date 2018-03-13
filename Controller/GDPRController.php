<?php

/*
 * This file is part of itk-dev/gdpr-bundle.
 *
 * (c) 2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\GDPRBundle\Controller;

use ItkDev\GDPRBundle\Helper\GDPRHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GDPRController extends Controller
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var \ItkDev\GDPRBundle\Helper\GDPRHelper */
    private $helper;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        GDPRHelper $helper
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->helper = $helper;
    }

    public function showAction(Request $request)
    {
        $form = $this->createGDPRForm($request->get('referrer'));

        return $this->render('ItkDevGDPRBundle:Default:index.html.twig', ['form' => $form->createView()]);
    }

    public function acceptAction(Request $request)
    {
        $form = $this->createGDPRForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && true === $form->get('accept')->getData()) {
            $token = $this->tokenStorage->getToken();
            if (null !== $token) {
                $user = $token->getUser();
                $this->helper->setGDPRAccepted($user);

                $referrer = $form->get('referrer')->getData();

                return $this->redirect($referrer ?: '/');
            }
        }

        return $this->showAction();
    }

    private function createGDPRForm($referrer = null)
    {
        return $this->createFormBuilder(['referrer' => $referrer])
            ->setAction($this->generateUrl('itk_dev_gdpr_accept'))
            ->setMethod('POST')
            ->add('accept', CheckboxType::class, [
                'required' => true,
                'label' => 'Accept GDPR',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Accept',
            ])
          ->add('referrer', HiddenType::class)
            ->getForm();
    }
}
