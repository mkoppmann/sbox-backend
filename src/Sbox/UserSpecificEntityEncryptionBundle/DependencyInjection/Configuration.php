<?php

namespace Sbox\UserSpecificEntityEncryptionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UndefinedMethod
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('sbox_user_specific_entity_encryption');

        // Grammar of config tree
        $rootNode
            ->children()
                ->scalarNode('user_class')->end()
                ->arrayNode('encryptable_entities')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('class')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
