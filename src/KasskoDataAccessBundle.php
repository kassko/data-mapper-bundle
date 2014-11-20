<?php

namespace Kassko\Bundle\DataAccessBundle;

use Kassko\Bundle\DataAccessBundle\DependencyInjection\Compiler\InitializeRegistryPass;
use Kassko\Bundle\DataAccessBundle\DependencyInjection\Compiler\RegisterClassToResolvePass;
use Kassko\Bundle\DataAccessBundle\DependencyInjection\Compiler\RegisterListenersToResolvePass;
use Kassko\Bundle\DataAccessBundle\DependencyInjection\Compiler\RegisterMappingLoadersPass;
use Kassko\DataAccess\ClassMetadata\Events;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KasskoDataAccessBundle extends Bundle
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
                    'kassko_data_access.container_aware_event_dispatcher',
                    Events::POST_LOAD_METADATA,
                    ''
                )
            )
        ;
    }
}
