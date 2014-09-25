<?php

namespace AG\JawboneUPInterfaceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ag_jawboneup_interface')
            ->children()
                ->scalarNode('client_id')
                    ->info('The client ID')
                ->end()
                ->scalarNode('client_secret')
                    ->info('The client API secret')
                ->end()
                ->scalarNode('callback')
                    ->info('The callback URL to pass to Jawbone UP API ')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
