<?php

/*
* This file is part of the Berny\OneTimeAccessBundle package
*
* (c) Berny Cantos <be@rny.cc>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Berny\OneTimeAccessBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class Factory implements SecurityFactoryInterface
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

    public function create(ContainerBuilder $container, $firewall, $config, $otaProvider, $entryPoint)
    {
        if (!empty($config['ota_provider'])) {
            $otaProvider = $config['ota_provider'];
        }

        $provider = "security.authentication.provider.one_time_access.{$firewall}";
        $container
            ->setDefinition($provider, new DefinitionDecorator('berny.ota.provider'))
            ->replaceArgument(0, $firewall)
            ->replaceArgument(1, new Reference($otaProvider))
        ;

        $listener = "security.authentication.listener.one_time_access.{$firewall}";
        $container
            ->setDefinition($listener, new DefinitionDecorator('berny.ota.firewall'))
            ->replaceArgument(0, $firewall)
            ->replaceArgument(1, $config)
        ;

        return array($provider, $listener, $entryPoint);
    }

    public function getKey()
    {
        return 'one_time_access';
    }

    public function getPosition()
    {
        return 'pre_auth';
    }
}
