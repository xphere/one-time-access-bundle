<?php

/*
* This file is part of the BCC\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace BCC\OneTimeAccessBundle\Security\Authentication\Provider;

use BCC\OneTimeAccessBundle\Security\Authentication\Token\OneTimeAccessToken;
use BCC\OneTimeAccessBundle\Security\Provider\OneTimeAccessProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class OneTimeAccessProvider implements AuthenticationProviderInterface
{
    private $providerKey;
    private $otaProvider;
    private $userChecker;

    public function __construct($providerKey, $otaProvider, UserCheckerInterface $userChecker)
    {
        $this->providerKey = $providerKey;
        if ($otaProvider instanceof OneTimeAccessProviderInterface === false) {
            throw new \InvalidArgumentException(
                'Provider must implement BCC\OTA\Security\ProviderInterface interface.'
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
        $user = $this->otaProvider->loadUserByOneTimeAccess($ota);

        if ($user) {
            $this->userChecker->checkPostAuth($user);
            $this->otaProvider->invalidateByOneTimeAccess($ota);
            $authenticatedToken = new OneTimeAccessToken($this->providerKey, $user);
            $authenticatedToken->setAttributes($token->getAttributes());
            $authenticatedToken->setAuthenticated(true);
            return $authenticatedToken;
        }
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OneTimeAccessToken && $token->getProviderKey() === $this->providerKey;
    }
}
