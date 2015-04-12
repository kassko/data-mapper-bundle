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
    private $expressionContextServiceName = 'kassko_data_mapper.expression_context';
    private $expressionFunctionProviderTag = 'kassko_data_mapper.expression_function_provider';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $expressionLanguageConfiguratorDef = $container->getDefinition($this->expressionLanguageConfiguratorServiceName);
        $expressionContextDef = $container->getDefinition($this->expressionContextServiceName);

        foreach ($container->findTaggedServiceIds($this->expressionFunctionProviderTag) as $service => $tagAttributes) {
            $expressionLanguageConfiguratorDef->addMethodCall('addProvider', [new Reference($service)]);

            foreach ($tagAttributes as $attributes) { 
                if (isset($attributes['variable_key'], $attributes['variable_value'])) {  
                    $expressionContextDef->addMethodCall('addVariable', [$attributes['variable_key'], new Reference($attributes['variable_value'])]);
                }
            }
        }  
    }
}
