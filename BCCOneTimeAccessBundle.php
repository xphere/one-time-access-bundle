<?php

/*
* This file is part of the BCC\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace BCC\OneTimeAccessBundle;

use BCC\OneTimeAccessBundle\DependencyInjection\Security\Factory\OneTimeAccessFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BCCOneTimeAccessBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $extension = $container->getExtension('security');
        $factory = new OneTimeAccessFactory();
        $extension->addSecurityListenerFactory($factory);
    }
}
