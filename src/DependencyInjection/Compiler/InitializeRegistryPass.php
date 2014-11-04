<?php

namespace Kassko\Bundle\DataAccessBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class InitializeRegistryPass implements CompilerPassInterface
{
    private static $registryInitializerId = 'kassko_data_access.registry_initializer';
    private static $registryItemTag = 'kassko_data_access.registry_item';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registryInitializerDef = $container->getDefinition(self::$registryInitializerId);

        foreach ($container->findTaggedServiceIds(self::$registryItemTag) as $service => $tagAttributes) {

            foreach ($tagAttributes as $attributes) {

                if (empty($attributes['key'])) {
                    throw new LogicException(sprintf('You should specify a key for the tag "%s" for service "%s".', self::$registryItemTag, $service));
                }

                $registryInitializerDef->addMethodCall('addItem', [$attributes['key'], new Reference($service)]);
            }
        }
    }
}
