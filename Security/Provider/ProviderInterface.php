<?php

/*
* This file is part of the Berny\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Berny\OneTimeAccessBundle\Security\Provider;

interface ProviderInterface
{
    function loadUserByOTA($ota);
    function invalidateByOTA($ota);
}
