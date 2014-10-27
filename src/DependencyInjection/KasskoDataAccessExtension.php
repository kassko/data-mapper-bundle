<?php

namespace Kassko\Bundle\DataAccessBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class KasskoDataAccessExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->configureMetadataCache($config, $container);
        $this->configureResultCache($config, $container);
        $this->configureMapping($config, $container);
    }

    private function configureMappingWithDefaults(array $config, ContainerBuilder $container)
    {

        if (! empty($config['mapping']['defaultResourceType'])) {

            $def = $container->getDefinition('data_access.configuration');
            $def->addMethodCall('setDefaultClassMetadataResourceType', [$config['mapping']['defaultResourceType']]);
        }
    }

    private function configureMapping(array $config, ContainerBuilder $container)
    {
        if (empty($config['mapping']['bundles'])) {

            $this->configureMappingWithDefaults($config, $container);
            return;
        }

        foreach ($config['mapping']['bundles'] as $bundleName => $bundleConfig) {

            //var_dump($bundleName, $bundleConfig);

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

                ////

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

        if (! empty ($config['metadata_cache']['class'])) {
            $cacheClass = $config['metadata_cache']['class'];
        } elseif (! empty($config['metadata_cache']['id'])) {
            $cacheId = $config['metadata_cache']['id'];
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
        $cacheAdapterDef = new Definition($config['metadata_cache']['adapter_class']);
        $cacheAdapterDef->addMethodCall('setWrappedCache', [new Reference($cacheId)]);
        $container->setDefinition($cacheAdapterId, $cacheAdapterDef);

        $cacheConfigId = 'data_access.configuration.class_metadata_cache';
        $cacheConfigDef = new DefinitionDecorator('data_access.configuration.cache.prototype');
        $cacheConfigDef->addMethodCall('setCache', [new Reference($cacheAdapterId)]);
        $cacheConfigDef->addMethodCall('setLifeTime', [$config['metadata_cache']['life_time']]);
        $cacheConfigDef->addMethodCall('setIsShared', [$config['metadata_cache']['is_shared']]);
        $container->setDefinition($cacheConfigId, $cacheConfigDef);

        $configDef = $container->getDefinition('data_access.configuration');
        $configDef->addMethodCall('setClassMetadataCacheConfig', [new Reference($cacheConfigId)]);
    }

    private function configureResultCache(array $config, ContainerBuilder $container)
    {
        $cacheClass = null;
        $cacheId = null;

        if (! empty($config['result_cache']['class'])) {
            $cacheClass = $config['result_cache']['class'];
        } elseif (! empty($config['result_cache']['id'])) {
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
        $cacheAdapterDef = new Definition($config['result_cache']['adapter_class']);
        $cacheAdapterDef->addMethodCall('setWrappedCache', [new Reference($cacheId)]);
        $container->setDefinition($cacheAdapterId, $cacheAdapterDef);

        $cacheConfigId = 'data_access.result_cache_configuration';
        $cacheConfigDef = new DefinitionDecorator('data_access.configuration.cache.prototype');
        $cacheConfigDef->addMethodCall('setCache', [new Reference($cacheAdapterId)]);
        $cacheConfigDef->addMethodCall('setLifeTime', [$config['metadata_cache']['life_time']]);
        $cacheConfigDef->addMethodCall('setShared', [$config['metadata_cache']['is_shared']]);
        $container->setDefinition($cacheConfigId, $cacheConfigDef);

        $configDef = $container->getDefinition('data_access.configuration');
        $configDef->addMethodCall('setResultCacheConfig', [new Reference($cacheConfigId)]);
    }
}