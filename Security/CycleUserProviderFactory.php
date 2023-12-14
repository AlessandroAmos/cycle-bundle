<?php

namespace Alms\Bundle\CycleBundle\Security;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\UserProvider\UserProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CycleUserProviderFactory implements UserProviderFactoryInterface
{

    public function create(ContainerBuilder $container, string $id, array $config): void
    {
        $container->setDefinition($id, new Definition(CycleUserProvider::class))
            ->addArgument(new Reference('cycle.orm.orm'))
            ->addArgument($config['class'])
            ->addArgument($config['property'])
        ;
    }

    public function getKey(): string
    {
        return 'cycle';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
        /** @var ArrayNodeDefinition $builder */
        $builder
            ->info('Configures a Cycle ORM entity class as a user provider.')
            ->children()
            ->scalarNode('class')->cannotBeEmpty()->info('The entity class to use as a user provider.')->end()
            ->scalarNode('property')->cannotBeEmpty()->info('The property of the entity class to use as the username.')
            ->end()
        ;
    }
}