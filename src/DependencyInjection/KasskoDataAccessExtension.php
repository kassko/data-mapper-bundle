<?php

namespace Kassko\Bundle\DataAccessBundle\DependencyInjection;

use Kassko\Common\Registry as CommonRegistry;
use Kassko\DataAccess\Registry\Registry as DataAccessRegistry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class KasskoDataAccessExtension extends Extension
{
    private $bridge;

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->bridge = CommonRegistry::getInstance()->getBridge();

        $this->configureLogger($config, $container);
        $this->configureLazyLoader($container);
        $this->configureMetadataCache($config['cache']['metadata_cache'], $container);
        $this->configureResultCache($config['cache']['result_cache'], $container);
        $this->configureMapping($config['mapping'], $container);
    }

    private function configureLogger(array $config, ContainerBuilder $container)
    {
        if (isset($config['logger_service'])) {

            $loggerServiceId = $config['logger_service'];
            $loggerDef = $container->getDefinition($loggerServiceId);
            $loggerDef->addTag('data_access.registry_item', ['key' => DataAccessRegistry::KEY_LOGGER]);

            $objectManagerDef = $container->getDefinition('data_access.object_manager');
            $objectManagerDef->addMethodCall('setLogger', [new Reference($loggerServiceId)]);
        }
    }

    private function configureLazyLoader(ContainerBuilder $container)
    {
        $lazyLoaderFactoryDef = $container->getDefinition('data_access.lazy_loader_factory');
        $lazyLoaderFactoryDef->addTag('data_access.registry_item', ['key' => DataAccessRegistry::KEY_LAZY_LOADER_FACTORY]);
    }

    private function configureMappingWithDefaults(array $config, ContainerBuilder $container)
    {
        if (! empty($config['defaultResourceType'])) {

            $def = $container->getDefinition('data_access.configuration');
            $def->addMethodCall('setDefaultClassMetadataResourceType', [$config['defaultResourceType']]);
        }
    }

    private function configureMapping(array $config, ContainerBuilder $container)
    {
        if (empty($config['bundles'])) {

            $this->configureMappingWithDefaults($config, $container);
            return;
        }

        foreach ($config['bundles'] as $bundleName => $bundleConfig) {

            $def = $container->getDefinition('data_access.configuration');

            $parentClassMetadataResourceType = $bundleConfig['type'];

            if (! empty($bundleConfig['resource_path'])) {
                $parentClassMetadataResourcePath = trim($bundleConfig['resource_path']);
            }

            if (! empty($bundleConfig['resource_dir'])) {
                $classMetadataResourceDir = $bundleConfig['resource_dir'];
            }

            foreach ($bundleConfig['entities'] as $entityName => $entityConfig) {

                if (! empty($entityConfig['type'])) {
                    $classMetadataResourceType = trim($entityConfig['type']);
                }

                if (! empty($entityConfig['resource_path'])) {
                    $classMetadataResource = trim($entityConfig['resource_path']);
                } elseif (! empty($entityConfig['resource_name'])) {
                    $classMetadataResource = $classMetadataResourceDir.'/'.$entityConfig['resource_name'];
                }

                $mappingEntityClass = trim($entityConfig['entity_class']);

                if (isset($classMetadataResourceType)) {
                    $def->addMethodCall('addClassMetadataResourceType', [$mappingEntityClass, $classMetadataResourceType]);
                } elseif (isset($parentClassMetadataResourceType)) {
                    $def->addMethodCall('addClassMetadataResourceType', [$mappingEntityClass, $parentClassMetadataResourceType]);
                }

                if (isset($classMetadataResource)) {
                    $def->addMethodCall('addClassMetadataResource', [$mappingEntityClass, $classMetadataResource]);
                }

                if (isset($classMetadataDir)) {
                    $def->addMethodCall('addClassMetadataDir', [$mappingEntityClass, $classMetadataDir]);
                } elseif (isset($parentClassMetadataDir)) {
                    $def->addMethodCall('addClassMetadataDir', [$mappingEntityClass, $parentClassMetadataDir]);
                }

            }
        }
    }

    private function configureMetadataCache(array $config, ContainerBuilder $container)
    {
        $cacheClass = null;
        $cacheId = null;

        if (! empty ($config['class'])) {
            $cacheClass = $config['class'];
        } elseif (! empty($config['id'])) {
            $cacheId = $config['id'];
        } else {
            $cacheClass = "Doctrine\\Common\\Cache\\ArrayCache";
        }

        if (null !== $cacheClass) {

            $cacheId = 'data_access.class_metadata_cache';
            $cacheDef = new DefinitionDecorator('data_access.class_metadata_cache.prototype');
            $cacheDef->setClass($cacheClass)->setPublic(false);
            $container->setDefinition($cacheId, $cacheDef);
        }

        $cacheAdapterId = $cacheId.'_adapter';
        $cacheAdapterDef = new Definition($this->bridge->getCacheAdapterClass(), [new Reference($cacheId)]);
        $container->setDefinition($cacheAdapterId, $cacheAdapterDef);

        $cacheConfigId = 'data_access.configuration.class_metadata_cache';
        $cacheConfigDef = new DefinitionDecorator('data_access.configuration.cache.prototype');
        $cacheConfigDef->addMethodCall('setCache', [new Reference($cacheAdapterId)]);
        $cacheConfigDef->addMethodCall('setLifeTime', [$config['life_time']]);
        $cacheConfigDef->addMethodCall('setShared', [$config['is_shared']]);
        $container->setDefinition($cacheConfigId, $cacheConfigDef);

        $configDef = $container->getDefinition('data_access.configuration');
        $configDef->addMethodCall('setClassMetadataCacheConfig', [new Reference($cacheConfigId)]);
    }

    private function configureResultCache(array $config, ContainerBuilder $container)
    {
        $cacheClass = null;
        $cacheId = null;

        if (! empty($config['class'])) {
            $cacheClass = $config['class'];
        } elseif (! empty($config['id'])) {
            $cacheId = $config['result_cache']['id'];
        } else {
            $cacheClass = "Doctrine\\Common\\Cache\\ArrayCache";
        }

        if (null !== $cacheClass) {

            $cacheId = 'data_access.result_cache';
            $cacheDef = new DefinitionDecorator('data_access.result_cache.prototype');
            $cacheDef->setClass($cacheClass)->setPublic(false);
            $container->setDefinition($cacheId, $cacheDef);
        }

        $cacheAdapterId = $cacheId.'_adapter';
        $cacheAdapterDef = new Definition($this->bridge->getCacheAdapterClass(), [new Reference($cacheId)]);
        $container->setDefinition($cacheAdapterId, $cacheAdapterDef);

        $cacheConfigId = 'data_access.result_cache_configuration';
        $cacheConfigDef = new DefinitionDecorator('data_access.configuration.cache.prototype');
        $cacheConfigDef->addMethodCall('setCache', [new Reference($cacheAdapterId)]);
        $cacheConfigDef->addMethodCall('setLifeTime', [$config['life_time']]);
        $cacheConfigDef->addMethodCall('setShared', [$config['is_shared']]);
        $container->setDefinition($cacheConfigId, $cacheConfigDef);

        $configDef = $container->getDefinition('data_access.configuration');
        $configDef->addMethodCall('setResultCacheConfig', [new Reference($cacheConfigId)]);
    }
}