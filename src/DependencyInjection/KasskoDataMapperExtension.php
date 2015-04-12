<?php

namespace Kassko\Bundle\DataMapperBundle\DependencyInjection;

use Kassko\DataMapper\Registry\Registry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class KasskoDataMapperExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('expression_language.xml');

        $this->configureLogger($config, $container);
        $this->configureLazyLoader($container);
        $this->configureConfiguration($config, $container);
    }

    private function configureLogger(array $config, ContainerBuilder $container)
    {
        if (isset($config['logger_service'])) {

            $loggerServiceId = $config['logger_service'];
            $loggerDef = $container->getDefinition($loggerServiceId);
            $loggerDef->addTag('kassko_data_mapper.registry_item', ['key' => Registry::KEY_LOGGER]);

            $objectManagerDef = $container->getDefinition('kassko_data_mapper.object_manager');
            $objectManagerDef->addMethodCall('setLogger', [new Reference($loggerServiceId)]);
        }
    }

    private function configureLazyLoader(ContainerBuilder $container)
    {
        $lazyLoaderFactoryDef = $container->getDefinition('kassko_data_mapper.lazy_loader_factory');
        $lazyLoaderFactoryDef->addTag('kassko_data_mapper.registry_item', ['key' => Registry::KEY_LAZY_LOADER_FACTORY]);
    }

    private function configureConfiguration(array $config, ContainerBuilder $container)
    {
        $configurationDef = $container->getDefinition('kassko_data_mapper.configuration');

        $this->configureMapping($config['mapping'], $container, $configurationDef);
        $this->configureMetadataCache($config['cache']['metadata_cache'], $container, $configurationDef);
        $this->configureResultCache($config['cache']['result_cache'], $container, $configurationDef);
    }

    private function configureMapping(array $config, ContainerBuilder $container, Definition $configurationDef)
    {
        $this->configureMappingWithDefaults($config, $container, $configurationDef);

        if (! isset($config['bundles'])) {
            return;
        }

        foreach ($config['bundles'] as $bundleName => $bundleConfig) {

            $parentClassMetadataResourceType = isset($bundleConfig['resource_type']) ? $bundleConfig['resource_type'] : null;
            $parentClassMetadataProviderMethod = isset($bundleConfig['provider_method']) ? $bundleConfig['provider_method'] : null;

            if (isset($bundleConfig['resource_path'])) {
                $parentClassMetadataResourcePath = trim($bundleConfig['resource_path']);
            }

            if (isset($bundleConfig['resource_dir'])) {
                $classMetadataResourceDir = $bundleConfig['resource_dir'];
            }

            foreach ($bundleConfig['objects'] as $objectName => $objectConfig) {

                if (isset($objectConfig['resource_type'])) {
                    $classMetadataResourceType = trim($objectConfig['resource_type']);
                }

                if (isset($objectConfig['resource_path'])) {
                    $classMetadataResource = trim($objectConfig['resource_path']);
                } elseif (isset($objectConfig['resource_name'])) {
                    $classMetadataResource = $classMetadataResourceDir.'/'.$objectConfig['resource_name'];
                }

                $mappingObjectClass = trim($objectConfig['class']);

                if (isset($classMetadataResourceType)) {
                    $configurationDef->addMethodCall('addClassMetadataResourceType', [$mappingObjectClass, $classMetadataResourceType]);
                } elseif (isset($parentClassMetadataResourceType)) {
                    $configurationDef->addMethodCall('addClassMetadataResourceType', [$mappingObjectClass, $parentClassMetadataResourceType]);
                }

                if (isset($classMetadataResource)) {
                    $configurationDef->addMethodCall('addClassMetadataResource', [$mappingObjectClass, $classMetadataResource]);
                }

                if (isset($classMetadataProviderMethod)) {
                    $configurationDef->addMethodCall('addClassMetadataProviderMethod', [$mappingObjectClass, $classMetadataResource]);
                } elseif (isset($parentClassMetadataProviderMethod)) {
                    $configurationDef->addMethodCall('addClassMetadataProviderMethod', [$mappingObjectClass, $parentClassMetadataProviderMethod]);
                }

                if (isset($classMetadataDir)) {
                    $configurationDef->addMethodCall('addClassMetadataDir', [$mappingObjectClass, $classMetadataDir]);
                } elseif (isset($parentClassMetadataDir)) {
                    $configurationDef->addMethodCall('addClassMetadataDir', [$mappingObjectClass, $parentClassMetadataDir]);
                }
            }
        }
    }

    private function configureMappingWithDefaults(array $config, ContainerBuilder $container, Definition $configurationDef)
    {
        if (isset($config['default_resource_type'])) {
            $configurationDef->addMethodCall('setDefaultClassMetadataResourceType', [$config['default_resource_type']]);
        }

        if (isset($config['default_resource_dir'])) {
            $configurationDef->addMethodCall('setDefaultClassMetadataResourceDir', [$config['default_resource_dir']]);
        }

        if (isset($config['default_provider_method'])) {
            $configurationDef->addMethodCall('setDefaultClassMetadataProviderMethod', [$config['default_provider_method']]);
        }
    }

    private function configureMetadataCache(array $config, ContainerBuilder $container, Definition $configurationDef)
    {
        $cacheClass = null;
        $cacheId = null;

        if (isset($config['class'])) {
            $cacheClass = $config['class'];
        } elseif (isset($config['id'])) {
            $cacheId = $config['id'];
        } else {
            $cacheClass = "Doctrine\\Common\\Cache\\ArrayCache";//TODO: use Kassko\Cache\ArrayCache instead
        }

        if (null !== $cacheClass) {

            $cacheId = 'kassko_data_mapper.class_metadata_cache';
            $cacheDef = new DefinitionDecorator('kassko_data_mapper.class_metadata_cache.prototype');
            $cacheDef->setClass($cacheClass)->setPublic(false);
            $container->setDefinition($cacheId, $cacheDef);
        }

        $cacheAdapterId = $cacheId.'_adapter';
        $cacheAdapterDef = new Definition($config['adapter_class'], [new Reference($cacheId)]);
        $container->setDefinition($cacheAdapterId, $cacheAdapterDef);

        $cacheConfigId = 'kassko_data_mapper.configuration.class_metadata_cache';
        $cacheConfigDef = new DefinitionDecorator('kassko_data_mapper.configuration.cache.prototype');
        $cacheConfigDef->addMethodCall('setCache', [new Reference($cacheAdapterId)]);
        $cacheConfigDef->addMethodCall('setLifeTime', [$config['life_time']]);
        $cacheConfigDef->addMethodCall('setShared', [$config['is_shared']]);
        $container->setDefinition($cacheConfigId, $cacheConfigDef);

        $configurationDef->addMethodCall('setClassMetadataCacheConfig', [new Reference($cacheConfigId)]);
    }

    private function configureResultCache(array $config, ContainerBuilder $container, Definition $configurationDef)
    {
        $cacheClass = null;
        $cacheId = null;

        if (isset($config['class'])) {
            $cacheClass = $config['class'];
        } elseif (isset($config['id'])) {
            $cacheId = $config['id'];
        } else {
            $cacheClass = "Doctrine\\Common\\Cache\\ArrayCache";
        }

        if (null !== $cacheClass) {

            $cacheId = 'kassko_data_mapper.result_cache';
            $cacheDef = new DefinitionDecorator('kassko_data_mapper.result_cache.prototype');
            $cacheDef->setClass($cacheClass)->setPublic(false);
            $container->setDefinition($cacheId, $cacheDef);
        }

        $cacheAdapterId = $cacheId.'_adapter';
        $cacheAdapterDef = new Definition($config['adapter_class'], [new Reference($cacheId)]);
        $container->setDefinition($cacheAdapterId, $cacheAdapterDef);

        $cacheConfigId = 'kassko_data_mapper.result_cache_configuration';
        $cacheConfigDef = new DefinitionDecorator('kassko_data_mapper.configuration.cache.prototype');
        $cacheConfigDef->addMethodCall('setCache', [new Reference($cacheAdapterId)]);
        $cacheConfigDef->addMethodCall('setLifeTime', [$config['life_time']]);
        $cacheConfigDef->addMethodCall('setShared', [$config['is_shared']]);
        $container->setDefinition($cacheConfigId, $cacheConfigDef);

        $configurationDef->addMethodCall('setResultCacheConfig', [new Reference($cacheConfigId)]);
    }
}
