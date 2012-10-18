<?php

/*
* This file is part of the BCC\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace BCC\OneTimeAccessBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class OneTimeAccessFactory implements SecurityFactoryInterface
{
    public function addConfiguration(NodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('route')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
                ->scalarNode('ota_provider')->end()
                ->scalarNode('parameter')
                    ->defaultValue('_token')
                ->end()
            ->end()
        ;
    }

    public function create(ContainerBuilder $container, $firewall, $config, $userProvider, $entryPoint)
    {
        if (!empty($config['ota_provider'])) {
            $userProvider = $config['ota_provider'];
        }

        $provider = 'security.authentication.provider.one_time_access.' . $firewall;
        $container
            ->setDefinition($provider, new DefinitionDecorator('bcc.one_time_access.provider'))
            ->replaceArgument(0, $firewall)
            ->replaceArgument(1, new Reference($userProvider))
        ;

        $listener = 'security.authentication.listener.one_time_access.' . $firewall;
        $container
            ->setDefinition($listener, new DefinitionDecorator('bcc.one_time_access.firewall'))
            ->replaceArgument(0, $firewall)
            ->replaceArgument(1, $config)
        ;

        return array($provider, $listener, $entryPoint);
    }

    public function getKey()
    {
        return 'bcc-one-time-access';
    }

    public function getPosition()
    {
        return 'remember_me';
    }
}
