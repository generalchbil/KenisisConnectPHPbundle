<?php

namespace KenisisConnectPHP\KenisisConnectPHPBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kenisis_connect');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->arrayNode("aws")
                    ->children()
                        ->scalarNode("key")->cannotBeEmpty()->end()
                        ->scalarNode("secret")->cannotBeEmpty()->end()
                        ->scalarNode("region")->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->scalarNode("api_dragibus_url_profile")->cannotBeEmpty()->end()
                ->scalarNode("api_dragibus_url_matching")->cannotBeEmpty()->end()
                ->scalarNode("api_get_segments")->cannotBeEmpty()->end()
                ->scalarNode('kinesis_stream_name')->cannotBeEmpty()->end()
            ->end()
        ;
        return $treeBuilder;
    }
}
