<?php

namespace Kassko\Bundle\DataAccessBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('kassko_data_access');

        $rootNode
            ->addDefaultsIfNotSet()
        	->children()
                ->arrayNode('cache')
                    ->append($this->addCacheNode('metadata_cache'))
                    ->append($this->addCacheNode('result_cache'))
                ->end()
                ->arrayNode('mapping')->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('defaultResourceType')->defaultValue('annotations')->end()
                        ->arrayNode('bundles')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('type')->defaultValue('annotations')->end()
                                    ->scalarNode('resource_dir')->end()

                                    ->arrayNode('entities')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('type')->end()
                                                ->scalarNode('resource_path')->end()
                                                ->scalarNode('resource_name')->end()
                                                ->scalarNode('entity_class')->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
        	->end()
        ;

        return $builder;
    }

    private function addCacheNode($name)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $node
            ->children()
                ->scalarNode('class')->end()
                ->scalarNode('id')->end()
                ->scalarNode('life_time')->defaultValue(0)->end()
                ->booleanNode('is_shared')->defaultFalse()->end()
                ->scalarNode('adapter_class')->defaultValue('Kassko\DataAccessBundle\Bridge\Adapter\FromDoctrineCacheAdapter')->end()
            ->end()
        ;

        return $node;
    }
}
