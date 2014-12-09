<?php

namespace Kassko\Bundle\DataMapperBundle;

use Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler\InitializeRegistryPass;
use Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler\RegisterClassToResolvePass;
use Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler\RegisterListenersToResolvePass;
use Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler\RegisterMappingLoadersPass;
use Kassko\DataMapper\ClassMetadata\Events;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KasskoDataMapperBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new RegisterListenersToResolvePass())
            ->addCompilerPass(new RegisterMappingLoadersPass())
            ->addCompilerPass(new InitializeRegistryPass())
            ->addCompilerPass(
                new RegisterListenersPass(
                    'kassko_data_mapper.container_aware_event_dispatcher',
                    Events::POST_LOAD_METADATA,
                    ''
                )
            )
        ;
    }
}
