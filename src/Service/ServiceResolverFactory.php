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

namespace Kassko\Bundle\DataMapperBundle\Service;

use Kassko\DataMapper\ServiceResolver;
use Psr\Container\ContainerInterface;

/**
 * Factory for creating a ServiceResolver configured with the Symfony container.
 *
 * This factory creates a ServiceResolver that can resolve services from
 * the Symfony dependency injection container, making all registered services
 * available to DataMapper data sources.
 */
class ServiceResolverFactory
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    /**
     * Create a ServiceResolver configured with the Symfony container.
     */
    public function create(): ServiceResolver
    {
        return new ServiceResolver($this->container);
    }
}
