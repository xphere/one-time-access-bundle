<?php

/*
* This file is part of the Berny\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Berny\OneTimeAccessBundle\Security\Authentication\Provider;

use Berny\OneTimeAccessBundle\Security\Authentication\Token\Token;
use Berny\OneTimeAccessBundle\Security\Provider\ProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class Provider implements AuthenticationProviderInterface
{
    private $providerKey;
    private $otaProvider;
    private $userChecker;

    public function __construct($providerKey, $otaProvider, UserCheckerInterface $userChecker)
    {
        $this->providerKey = $providerKey;
        if ($otaProvider instanceof ProviderInterface === false) {
            throw new \InvalidArgumentException(
                'Provider must implement Berny\OneTimeAccessBundle\Security\ProviderInterface interface.'
            );
        }
        $this->otaProvider = $otaProvider;
        $this->userChecker = $userChecker;
    }

    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return;
        }

        $ota = $token->getCredentials();
        $user = $this->otaProvider->loadUserByOTA($ota);

        if ($user) {
            $this->userChecker->checkPostAuth($user);
            $this->otaProvider->invalidateByOTA($ota);
            $authenticatedToken = new Token($this->providerKey, $user);
            $authenticatedToken->setAttributes($token->getAttributes());
            $authenticatedToken->setAuthenticated(true);
            return $authenticatedToken;
        }
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof Token && $token->getProviderKey() === $this->providerKey;
    }
}
