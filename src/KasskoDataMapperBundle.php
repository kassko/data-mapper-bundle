<?php

declare(strict_types=1);

/*
 * This file is part of DataMapperBundle.
 *
 * Copyright 2025 kassko
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\Bundle\DataMapperBundle;

use Kassko\Bundle\DataMapperBundle\DependencyInjection\KasskoDataMapperExtension;
use Kassko\DataMapper\DataMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * DataMapperBundle provides Symfony integration for the DataMapper library.
 *
 * The bundle initializes the DataMapper context during the boot phase,
 * which sets up the Loader and Context registries needed by data objects.
 * This allows data objects to remain serializable (no direct dependency on the loader)
 * while still having access to lazy loading capabilities.
 */
class KasskoDataMapperBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        $this->initDataMapper();
    }

    /**
     * {@inheritdoc}
     *
     * Override to use 'kassko_data_mapper' as the extension alias.
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new KasskoDataMapperExtension();
        }

        return $this->extension;
    }

    /**
     * Initialize the DataMapper context.
     *
     * This initialization creates the context that data objects will use.
     * The context includes the Loader that orchestrates property hydration.
     * By triggering this in boot(), we ensure the context is available
     * before any data objects are used.
     */
    private function initDataMapper(): void
    {
        // Getting the DataMapper service triggers its construction,
        // which registers the Loader and sets up the context registries.
        // We don't need to store the instance - just trigger the initialization.
        $this->container->get('kassko_data_mapper.data_mapper');
    }
}
