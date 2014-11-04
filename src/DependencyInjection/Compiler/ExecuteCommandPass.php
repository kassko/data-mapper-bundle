<?php

namespace Kassko\Bundle\DataAccessBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class ExecuteCommandPass implements CompilerPassInterface
{
    private static $objectManagerId = 'kassko_data_access.object_manager';
    private static $commandTag = 'kassko_data_access.command';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $objectManagerDef = $container->getDefinition(self::$objectManagerId);

        foreach ($container->findTaggedServiceIds(self::$commandTag) as $service => $tagAttributes) {

            foreach ($tagAttributes as $attributes) {

                if (empty($attributes['method'])) {
                    $objectManagerDef->addMethodCall('executeCommand', [new Reference($service)]);
                } else {
                    $objectManagerDef->addMethodCall('executeCommand', [[new Reference($service), $attributes['method']]]);
                }
            }
        }
    }
}
