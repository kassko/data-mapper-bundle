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

use Kassko\Bundle\DataMapperBundle\DataCollector\DataMapperDataCollector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * DataMapperBundle extension for Symfony DependencyInjection.
 *
 * This extension loads the bundle's service configuration and processes
 * the bundle configuration to set up DataMapper services.
 */
class DataMapperExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Load services from YAML configuration
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');

        // Store configuration as parameters
        $container->setParameter('kassko_data_mapper.enable_lineage_collection', $config['enable_lineage_collection']);
        $container->setParameter('kassko_data_mapper.enable_profiler', $config['enable_profiler']);
        $container->setParameter('kassko_data_mapper.cache.enabled', $config['cache']['enabled']);
        $container->setParameter('kassko_data_mapper.cache.service', $config['cache']['service']);
        $container->setParameter('kassko_data_mapper.logger.enabled', $config['logger']['enabled']);
        $container->setParameter('kassko_data_mapper.logger.service', $config['logger']['service']);
        $container->setParameter('kassko_data_mapper.logger.channel', $config['logger']['channel']);
        $container->setParameter('kassko_data_mapper.validation.paths', $config['validation']['paths']);
        $container->setParameter('kassko_data_mapper.validation.namespaces', $config['validation']['namespaces']);

        // Configure cache service if enabled
        $this->configureCache($container, $config['cache']);

        // Configure logger service
        $this->configureLogger($container, $config['logger']);

        // Configure profiler if enabled and in debug mode
        $this->configureProfiler($container, $config);
    }

    /**
     * Configure cache service for DataMapper.
     */
    private function configureCache(ContainerBuilder $container, array $cacheConfig): void
    {
        if (!$cacheConfig['enabled'] || $cacheConfig['service'] === null) {
            return;
        }

        $dataMapperDefinition = $container->getDefinition('kassko_data_mapper.data_mapper');
        $dataMapperDefinition->replaceArgument(1, new Reference($cacheConfig['service']));
    }

    /**
     * Configure logger service for DataMapper.
     */
    private function configureLogger(ContainerBuilder $container, array $loggerConfig): void
    {
        if (!$loggerConfig['enabled']) {
            return;
        }

        // Use monolog channel if available, otherwise use the configured service
        $loggerService = $loggerConfig['service'];
        if ($loggerConfig['channel'] !== null && $container->hasDefinition('monolog.logger')) {
            $loggerService = 'monolog.logger.' . $loggerConfig['channel'];
        }

        if ($container->has($loggerService) || $container->hasDefinition($loggerService)) {
            $dataMapperDefinition = $container->getDefinition('kassko_data_mapper.data_mapper');
            $dataMapperDefinition->replaceArgument(2, new Reference($loggerService));
        }
    }

    /**
     * Configure profiler integration.
     */
    private function configureProfiler(ContainerBuilder $container, array $config): void
    {
        // Check if profiler is enabled and web profiler bundle is available
        if (!$config['enable_profiler']) {
            $container->removeDefinition('kassko_data_mapper.data_collector');
            return;
        }

        // In production, we might want to disable the profiler anyway
        // The actual check for debug mode happens at runtime via the data collector
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'kassko_data_mapper';
    }
}
