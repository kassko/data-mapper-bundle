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

use Kassko\Bundle\DataMapperBundle\Service\DataMapperFactory;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ServiceResolver;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class DataMapperFactoryTest extends TestCase
{
    private DataMapperFactory $factory;
    private ServiceResolver $serviceResolver;

    protected function setUp(): void
    {
        $this->factory = new DataMapperFactory();
        // Use real ServiceResolver instance since it's final and cannot be mocked
        $this->serviceResolver = new ServiceResolver();
    }

    public function testCreateWithMinimalConfiguration(): void
    {
        $dataMapper = $this->factory->create(
            $this->serviceResolver,
            null,
            null,
            [],
            [],
            'show'
        );

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testCreateWithCacheAndLogger(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $dataMapper = $this->factory->create(
            $this->serviceResolver,
            $cache,
            $logger,
            [],
            [],
            'show'
        );

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testCreateWithCustomHydrators(): void
    {
        $dataMapper = $this->factory->create(
            $this->serviceResolver,
            null,
            null,
            [
                'datetime' => 'app.hydrator.datetime',
                'money' => 'app.hydrator.money',
            ],
            [],
            'show'
        );

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testCreateWithSensitiveKeys(): void
    {
        $dataMapper = $this->factory->create(
            $this->serviceResolver,
            null,
            null,
            [],
            [
                'password' => 'hide',
                'api_key' => 'mask',
                'email' => 'show',
            ],
            'show'
        );

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testCreateWithDefaultSensitiveLevelMask(): void
    {
        $dataMapper = $this->factory->create(
            $this->serviceResolver,
            null,
            null,
            [],
            [],
            'mask'
        );

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testCreateWithDefaultSensitiveLevelHide(): void
    {
        $dataMapper = $this->factory->create(
            $this->serviceResolver,
            null,
            null,
            [],
            [],
            'hide'
        );

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }

    public function testCreateWithAllOptions(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $dataMapper = $this->factory->create(
            $this->serviceResolver,
            $cache,
            $logger,
            ['custom' => 'app.hydrator.custom'],
            ['secret' => 'hide', 'token' => 'mask'],
            'mask'
        );

        $this->assertInstanceOf(DataMapper::class, $dataMapper);
    }
}
