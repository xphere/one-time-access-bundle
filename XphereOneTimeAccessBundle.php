<?php

/*
* This file is part of the Xphere\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Xphere\OneTimeAccessBundle;

use Xphere\OneTimeAccessBundle\DependencyInjection\Security\Factory\Factory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class XphereOneTimeAccessBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $extension = $container->getExtension('security');
        $factory = new Factory();
        $extension->addSecurityListenerFactory($factory);
    }
}
