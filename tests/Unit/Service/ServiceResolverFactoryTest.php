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

namespace Kassko\Bundle\DataMapperBundle\Tests\Unit\Service;

use Kassko\Bundle\DataMapperBundle\Service\ServiceResolverFactory;
use Kassko\DataMapper\ServiceResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ServiceResolverFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory = new ServiceResolverFactory($container);

        $serviceResolver = $factory->create();

        $this->assertInstanceOf(ServiceResolver::class, $serviceResolver);
    }
}
