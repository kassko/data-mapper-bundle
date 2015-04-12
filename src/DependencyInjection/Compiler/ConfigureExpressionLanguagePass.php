<?php

namespace Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class ConfigureExpressionLanguagePass implements CompilerPassInterface
{
    private $expressionLanguageConfiguratorServiceName = 'kassko_data_mapper.expression_language.configurator';
    private $expressionFunctionProviderTag = 'kassko_data_mapper.expression_function_provider';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $expressionLanguageConfiguratorDef = $container->getDefinition($this->expressionLanguageConfiguratorServiceName);

        foreach ($container->findTaggedServiceIds($this->expressionFunctionProviderTag) as $service => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $expressionLanguageConfiguratorDef->addMethodCall('addProvider', [new Reference($service)]);
                break;//Read only one tag.
            }
        }
    }
}
