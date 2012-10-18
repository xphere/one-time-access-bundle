<?php

/*
* This file is part of the BCC\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace BCC\OneTimeAccessBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

class OneTimeAccessToken extends AbstractToken
{
    private $providerKey;
    private $credentials;

    public function __construct($providerKey, UserInterface $user = null)
    {
        if ($user) {
            parent::__construct($user->getRoles());
            $this->setUser($user);
        } else {
            parent::__construct();
        }
        $this->providerKey = $providerKey;
    }

    public function getProviderKey()
    {
        return $this->providerKey;
    }

    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;
    }

    public function getCredentials()
    {
        return $this->credentials;
    }
}
