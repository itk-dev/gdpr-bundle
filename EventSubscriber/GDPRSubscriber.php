<?php

/*
 * This file is part of itk-dev/gdpr-bundle.
 *
 * (c) 2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\GDPRBundle\EventSubscriber;

use AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class GDPRSubscriber implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var TokenStorage */
    private $tokenStorage;

    /** @var RouterInterface */
    private $router;

    /** @var array */
    private $configuration;

    public function __construct(
        RequestStack $requestStack,
        TokenStorage $tokenStorage,
        RouterInterface $router,
        array $configuration
    ) {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->configuration = $configuration;
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
        if ($user instanceof User && null === $user->getGdprAcceptedAt()) {
            $redirectUrl = null;
            if (isset($this->configuration['accept_route'])) {
                $routeName = $this->configuration['accept_route'];
                $routeParameters = isset($this->configuration['accept_route_parameters'])
                    ? $this->configuration['accept_route_parameters'] : [];
                $redirectUrl = $this->router->generate($routeName, $routeParameters);
            } elseif (isset($this->configuration['accept_url'])) {
                $redirectUrl = $this->configuration['accept_url'];
            }

            if (null === $redirectUrl) {
                throw new \RuntimeException('GDPR not configured correctly.');
            }

            $currentPath = $request->getPathInfo();
            $redirectInfo = parse_url($redirectUrl);

            $onRedirectUrl = $redirectInfo['path'] === $currentPath;

            if (!$onRedirectUrl) {
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
