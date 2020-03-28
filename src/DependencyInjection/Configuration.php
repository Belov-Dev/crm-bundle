<?php

namespace A2Global\CRMBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('a2crm');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->booleanNode('i_am_familiar_with_symfony')->info('Whether you familiar or not with symfony')->defaultTrue()->end()
            ->integerNode('my_age')->defaultValue(3)->info('Your actual age (Don`t forget to update it every year)')->end()
            ->scalarNode('manage_url')->defaultValue('/manage')->info('Desired url to CRM homepage')->end()
            ->end();

        return $treeBuilder;
    }
}