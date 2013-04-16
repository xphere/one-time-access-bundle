<?php

/*
* This file is part of the Berny\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Berny\OneTimeAccessBundle\Security\Http\Firewall;

use Berny\OneTimeAccessBundle\Security\Authentication\Token\Token;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Request;

class Firewall implements ListenerInterface
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

        if ($this->isRoute($request) && ($key = $this->getKey($request)) && ($token = $this->getToken($key))) {
            $this->context->setToken($token);
            if ($this->dispatcher !== null && $this->dispatcher->hasListeners(SecurityEvents::INTERACTIVE_LOGIN)) {
                $loginEvent = new InteractiveLoginEvent($request, $token);
                $this->dispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
            }
        }
    }

    protected function isRoute(Request $request)
    {
        return $request->attributes->get('_route') === $this->route;
    }

    protected function getKey(Request $request)
    {
        if ($request->attributes->has($this->parameter)) {
            return $request->attributes->get($this->parameter);
        }
    }

    protected function getToken($key)
    {
        $token = new Token($this->providerKey);
        $token->setCredentials($key);
        return $this->authenticator->authenticate($token);
    }
}
