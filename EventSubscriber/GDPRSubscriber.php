<?php

/*
 * This file is part of itk-dev/gdpr-bundle.
 *
 * (c) 2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\GDPRBundle\EventSubscriber;

use FOS\UserBundle\Model\User;
use ItkDev\GDPRBundle\Helper\GDPRHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class GDPRSubscriber implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var \ItkDev\GDPRBundle\Helper\GDPRHelper */
    private $helper;

    public function __construct(
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        GDPRHelper $helper
    ) {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->helper = $helper;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'checkGDPRrequest',
        ];
    }

    public function checkGDPRrequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST !== $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request || 'GET' !== $request->getMethod()) {
            return;
        }

        $user = $token->getUser();
        if ($user instanceof User && !$this->helper->isGDPRAccepted($user)) {
            $redirectUrl = $this->helper->getRedirectUrl();

            $currentPath = $request->getPathInfo();
            $redirectInfo = parse_url($redirectUrl);

            // Only redirect if not already on redirect target path.
            $doRedirect = $redirectInfo['path'] !== $currentPath;

            if ($doRedirect) {
                // Add current url to redirect url.
                $referrer = $request->getPathInfo();
                if (null !== $request->getQueryString()) {
                    $referrer .= '?'.$request->getQueryString();
                }
                $redirectUrl .= (false === strpos($redirectUrl, '?') ? '?' : '&')
                  .'referrer='.urlencode($referrer);
                $event->setResponse(new RedirectResponse($redirectUrl));
            }
        }
    }
}
