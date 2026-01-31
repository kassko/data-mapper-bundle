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
 * KasskoDataMapperBundle extension for Symfony DependencyInjection.
 *
 * This extension loads the bundle's service configuration and processes
 * the bundle configuration to set up DataMapper services.
 */
class KasskoDataMapperExtension extends Extension
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
        $container->setParameter('kassko_data_mapper.enable_cascade_collection', $config['enable_cascade_collection']);
        $container->setParameter('kassko_data_mapper.enable_profiler', $config['enable_profiler']);
        $container->setParameter('kassko_data_mapper.data_source_cache.enabled', $config['data_source_cache']['enabled']);
        $container->setParameter('kassko_data_mapper.data_source_cache.service', $config['data_source_cache']['service']);
        $container->setParameter('kassko_data_mapper.mapping_cache.enabled', $config['mapping_cache']['enabled']);
        $container->setParameter('kassko_data_mapper.mapping_cache.service', $config['mapping_cache']['service']);
        $container->setParameter('kassko_data_mapper.mapping_strategy.enabled', $config['mapping_strategy']['enabled']);
        $container->setParameter('kassko_data_mapper.logger.enabled', $config['logger']['enabled']);
        $container->setParameter('kassko_data_mapper.logger.service', $config['logger']['service']);
        $container->setParameter('kassko_data_mapper.logger.channel', $config['logger']['channel']);
        $container->setParameter('kassko_data_mapper.validation.paths', $config['validation']['paths']);
        $container->setParameter('kassko_data_mapper.validation.namespaces', $config['validation']['namespaces']);
        $container->setParameter('kassko_data_mapper.custom_hydrators', $this->processCustomHydrators($config['custom_hydrators']));
        $container->setParameter('kassko_data_mapper.custom_object_mappers', $this->processCustomObjectMappers($config['custom_object_mappers']));
        $container->setParameter('kassko_data_mapper.sensitive_keys', $this->processSensitiveKeys($config['sensitive_keys']));
        $container->setParameter('kassko_data_mapper.default_sensitive_level', $config['default_sensitive_level']);

        // Configure data source cache service if enabled
        $this->configureDataSourceCache($container, $config['data_source_cache']);

        // Configure mapping cache service if enabled
        $this->configureMappingCache($container, $config['mapping_cache']);

        // Configure mapping strategy
        $this->configureMappingStrategy($container, $config['mapping_strategy']);

        // Configure logger service
        $this->configureLogger($container, $config['logger']);

        // Configure profiler if enabled and in debug mode
        $this->configureProfiler($container, $config);

        if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID < 60000) {
            // It is disabled in Symfony < 6.0 to avoid issues with early container access
            if ($container->hasDefinition('kassko_data_mapper.value_resolver.handle_object')) {
                $container->removeDefinition('kassko_data_mapper.value_resolver.handle_object');
            }
        }
    }

    /**
     * Configure data source cache service for DataMapper.
     */
    private function configureDataSourceCache(ContainerBuilder $container, array $cacheConfig): void
    {
        if (!$cacheConfig['enabled'] || $cacheConfig['service'] === null) {
            return;
        }

        $dataMapperDefinition = $container->getDefinition('kassko_data_mapper.data_mapper');
        $dataMapperDefinition->replaceArgument('$dataSourceCache', new Reference($cacheConfig['service']));
    }

    /**
     * Configure mapping cache service for DataMapper.
     */
    private function configureMappingCache(ContainerBuilder $container, array $cacheConfig): void
    {
        if (!$cacheConfig['enabled'] || $cacheConfig['service'] === null) {
            return;
        }

        $dataMapperDefinition = $container->getDefinition('kassko_data_mapper.data_mapper');
        $dataMapperDefinition->replaceArgument('$mappingCache', new Reference($cacheConfig['service']));
    }

    /**
     * Configure mapping strategy feature.
     */
    private function configureMappingStrategy(ContainerBuilder $container, array $config): void
    {
        if (!$config['enabled']) {
            return;
        }

        $dataMapperDefinition = $container->getDefinition('kassko_data_mapper.data_mapper');
        $dataMapperDefinition->replaceArgument('$mappingStrategyEnabled', true);
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
            $dataMapperDefinition->replaceArgument('$logger', new Reference($loggerService));
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
     * Process custom hydrators configuration to service references.
     *
     * @param array<string, string> $hydrators Hydrator name => service id pairs
     * @return array<string, string> Processed hydrators
     */
    private function processCustomHydrators(array $hydrators): array
    {
        // Return as-is - service references are resolved at runtime via ServiceResolver
        return $hydrators;
    }

    /**
     * Process custom object mappers configuration to service references.
     *
     * @param array<string, string> $objectMappers Object mapper name => service id pairs
     * @return array<string, string> Processed object mappers
     */
    private function processCustomObjectMappers(array $objectMappers): array
    {
        // Return as-is - service references are resolved at runtime via ServiceResolver
        return $objectMappers;
    }

    /**
     * Process sensitive keys configuration to SensitiveLevel enum values.
     *
     * @param array<string, string> $sensitiveKeys Key => level string pairs
     * @return array<string, string> Processed sensitive keys (enum conversion happens in DataMapper)
     */
    private function processSensitiveKeys(array $sensitiveKeys): array
    {
        // Return as-is - SensitiveLevel enum conversion happens in DataMapper constructor
        return $sensitiveKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): string
    {
        return 'kassko_data_mapper';
    }
}
