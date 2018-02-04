<?php

namespace MongoDBBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mongodb_client');

        $rootNode
            ->children()
                ->arrayNode('connection')
                    ->children()
                        ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('port')->cannotBeEmpty()->defaultValue(27017)->end()
                        ->scalarNode('user')->defaultNull()->end()
                        ->scalarNode('pass')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('default_database')->defaultNull()->end()
                ->arrayNode('options')
                    ->prototype('scalar')
                    ->end()
                ->end();

        return $treeBuilder;
    }
}