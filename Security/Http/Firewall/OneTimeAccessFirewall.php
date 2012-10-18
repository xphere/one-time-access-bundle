<?php

/*
* This file is part of the BCC\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace BCC\OneTimeAccessBundle\Security\Http\Firewall;

use BCC\OneTimeAccessBundle\Security\Authentication\Token\OneTimeAccessToken;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class OneTimeAccessFirewall implements ListenerInterface
{
    private $providerKey;
    private $route;
    private $parameter;
    private $context;
    private $authenticator;
    private $dispatcher;

    public function __construct($providerKey, array $options, SecurityContextInterface $context, AuthenticationManagerInterface $authenticator, EventDispatcherInterface $dispatcher = null)
    {
        $this->providerKey = $providerKey;
        $this->route = $options['route'];
        $this->parameter = $options['parameter'];
        $this->context = $context;
        $this->authenticator = $authenticator;
        $this->dispatcher = $dispatcher;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route') === $this->route) {
            $token = new OneTimeAccessToken($this->providerKey);
            if ($request->attributes->has($this->parameter)) {
                $key = $request->attributes->get($this->parameter);
                $token->setCredentials($key);
                $authenticatedToken = $this->authenticator->authenticate($token);
                if ($authenticatedToken) {
                    $this->context->setToken($authenticatedToken);
                    if ($this->dispatcher !== null && $this->dispatcher->hasListeners(SecurityEvents::INTERACTIVE_LOGIN)) {
                        $loginEvent = new InteractiveLoginEvent($request, $authenticatedToken);
                        $this->dispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
                    }
                }
            }
        }
    }
}
