<?php

/*
 * This file is part of itk-dev/gdpr-bundle.
 *
 * (c) 2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ItkDev\GDPRBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('itk_dev_gdpr');

        $rootNode
            ->children()
                ->scalarNode('accept_url')->end()
                ->scalarNode('accept_route')->defaultValue('itk_dev_gdpr_show')->end()
                ->variableNode('accept_route_parameters')->end()
                ->scalarNode('user_gdpr_property')->defaultValue('gdprAcceptedAt')->end()
            ->end();

        return $treeBuilder;
    }
}
