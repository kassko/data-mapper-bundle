<?php

namespace Kassko\Bundle\DataMapperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        list($rootNode, $builder) = $this->getRootNode('kassko_data_mapper');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('mapping')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_resource_type')->defaultValue('annotations')->end()
                        ->scalarNode('default_resource_dir')->end()
                        ->scalarNode('default_provider_method')->end()
                        ->arrayNode('bundles')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('resource_type')->end()
                                    ->scalarNode('resource_dir')->end()
                                    ->scalarNode('provider_method')->end()

                                    ->arrayNode('objects')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('class')->isRequired()->end()
                                                ->scalarNode('resource_type')->end()
                                                ->scalarNode('resource_path')->end()
                                                ->scalarNode('resource_name')->end()
                                                ->scalarNode('provider_method')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('cache')->addDefaultsIfNotSet()
                    ->append($this->addCacheNode('metadata_cache'))
                    ->append($this->addCacheNode('result_cache'))
                ->end()

                ->scalarNode('logger')->end()
            ->end()
        ;

        return $builder;
    }

    private function addCacheNode($name)
    {
        list($node, $builder) = $this->getRootNode($name);

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('class')->end()
                ->scalarNode('id')->end()
                ->scalarNode('life_time')->defaultValue(0)->end()
                ->booleanNode('is_shared')->defaultFalse()->end()
                ->scalarNode('adapter_class')->defaultValue('Kassko\Bundle\DataMapperBundle\Adapter\Cache\DoctrineCacheAdapter')->end()
            ->end()
        ;

        return $node;
    }

    private function getRootNode($rootNodeName)
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $builder = new TreeBuilder($rootNodeName);
            $rootNode = $builder->getRootNode();
        } else {//Keep compatibility with Symfony <= 4.3
            /**
             * @see https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/Config/Definition/Builder/TreeBuilder.php#L48
             */
            $builder = new TreeBuilder;
            $rootNode = $builder->root($rootNodeName);
        }

        return [$rootNode, $builder];
    }
}
