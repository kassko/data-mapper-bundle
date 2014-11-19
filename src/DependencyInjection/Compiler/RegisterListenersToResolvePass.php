<?php

namespace Kassko\Bundle\DataAccessBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use LogicException;

class RegisterListenersToResolvePass implements CompilerPassInterface
{
    private static $classesToRegisterTag = 'kassko_data_access.listener';
    private static $classeResolverId = 'kassko_data_access.object_listener_resolver';

    public function __construct($classesToRegisterTag = null)
    {
        if (null !== $classesToRegisterTag) {
            self::$classesToRegisterTag = $classesToRegisterTag;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $classResolverDef = $container->getDefinition(self::$classeResolverId);

        foreach ($container->findTaggedServiceIds(self::$classesToRegisterTag) as $service => $tagAttributes) {

            foreach ($tagAttributes as $attributes) {

                $class = $container->getDefinition($service)->getClass();
                if (empty($class)) {
                    throw new LogicException(sprintf("A class should be specified for the service '%s'.", $service));
                }

                $classResolverDef->addMethodCall('registerClass', [$class, $service]);
            }
        }
    }
}
