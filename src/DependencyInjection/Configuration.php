<?php

declare(strict_types=1);

/*
 * This file is part of DataMapperBundle.
 *
 * Copyright 2025 kassko
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\Bundle\DataMapperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for DataMapperBundle.
 *
 * This class defines the structure of the bundle's configuration.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('kassko_data_mapper');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                // Enable/disable the data lineage collector
                ->booleanNode('enable_lineage_collection')
                    ->defaultFalse()
                    ->info('Enable data lineage collection for debugging purposes.')
                ->end()

                // Enable/disable the attribute cascade collector
                ->booleanNode('enable_cascade_collection')
                    ->defaultFalse()
                    ->info('Enable attribute cascade collection for debugging attribute inheritance.')
                ->end()

                // Enable/disable profiler integration (only works in dev/debug mode)
                ->booleanNode('enable_profiler')
                    ->defaultTrue()
                    ->info('Enable Symfony profiler integration for data lineage visualization.')
                ->end()

                // Cache configuration
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                            ->info('Enable caching for metadata.')
                        ->end()
                        ->scalarNode('service')
                            ->defaultNull()
                            ->info('PSR-16 cache service ID (e.g., "cache.app").')
                        ->end()
                    ->end()
                ->end()

                // Logger configuration
                ->arrayNode('logger')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Enable logging for DataMapper operations.')
                        ->end()
                        ->scalarNode('service')
                            ->defaultValue('logger')
                            ->info('PSR-3 logger service ID.')
                        ->end()
                        ->scalarNode('channel')
                            ->defaultValue('data_mapper')
                            ->info('Logger channel name.')
                        ->end()
                    ->end()
                ->end()

                // Paths for validation commands
                ->arrayNode('validation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('paths')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                            ->info('Paths to scan for data object classes during validation.')
                        ->end()
                        ->arrayNode('namespaces')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                            ->info('Namespaces to use when resolving class names.')
                        ->end()
                    ->end()
                ->end()

                // Custom hydrators configuration
                ->arrayNode('custom_hydrators')
                    ->useAttributeAsKey('name')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                    ->info('Custom hydrators as key => service_id pairs.')
                ->end()

                // Sensitive keys configuration for data lineage
                ->arrayNode('sensitive_keys')
                    ->useAttributeAsKey('name')
                    ->enumPrototype()
                        ->values(['show', 'mask', 'hide'])
                    ->end()
                    ->defaultValue([])
                    ->info('Global sensitive keys configuration (key => level). Levels: show, mask, hide.')
                ->end()

                // Default sensitive level for all properties
                ->enumNode('default_sensitive_level')
                    ->values(['show', 'mask', 'hide'])
                    ->defaultValue('show')
                    ->info('Default sensitive level for all properties in data lineage.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
