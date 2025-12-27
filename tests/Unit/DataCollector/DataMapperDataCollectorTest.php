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

namespace Kassko\Bundle\DataMapperBundle\Tests\Unit\DataCollector;

use Kassko\Bundle\DataMapperBundle\DataCollector\DataMapperDataCollector;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ServiceResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DataMapperDataCollectorTest extends TestCase
{
    public function testCollectWhenDisabled(): void
    {
        $collector = new DataMapperDataCollector(enabled: false);

        $collector->collect(
            new Request(),
            new Response()
        );

        $this->assertFalse($collector->isEnabled());
        $this->assertEmpty($collector->getEvents());
        $this->assertEmpty($collector->getSummary());
    }

    public function testCollectWithoutDataMapper(): void
    {
        $collector = new DataMapperDataCollector(enabled: true);

        $collector->collect(
            new Request(),
            new Response()
        );

        $this->assertFalse($collector->isEnabled());
        $this->assertEmpty($collector->getEvents());
    }

    public function testCollectWithDataMapper(): void
    {
        $collector = new DataMapperDataCollector(enabled: true);

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $dataMapper->enableLineageCollection();

        $collector->setDataMapper($dataMapper);
        $collector->collect(
            new Request(),
            new Response()
        );

        $this->assertTrue($collector->isEnabled());
        $this->assertIsArray($collector->getEvents());
        $this->assertIsArray($collector->getSummary());
    }

    public function testGetName(): void
    {
        $collector = new DataMapperDataCollector();

        $this->assertEquals('kassko_data_mapper', $collector->getName());
    }

    public function testGetTemplate(): void
    {
        $this->assertEquals(
            '@DataMapper/Collector/data_mapper.html.twig',
            DataMapperDataCollector::getTemplate()
        );
    }

    public function testReset(): void
    {
        $collector = new DataMapperDataCollector(enabled: true);

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $dataMapper->enableLineageCollection();

        $collector->setDataMapper($dataMapper);
        $collector->collect(
            new Request(),
            new Response()
        );

        $collector->reset();

        // After reset, data should be empty
        $this->assertEmpty($collector->getEvents());
    }

    public function testGetEventCount(): void
    {
        $collector = new DataMapperDataCollector(enabled: true);

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);

        $collector->setDataMapper($dataMapper);
        $collector->collect(
            new Request(),
            new Response()
        );

        $this->assertIsInt($collector->getEventCount());
    }

    public function testGetEventsByType(): void
    {
        $collector = new DataMapperDataCollector(enabled: true);

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $dataMapper->enableLineageCollection();

        $collector->setDataMapper($dataMapper);
        $collector->collect(
            new Request(),
            new Response()
        );

        $this->assertIsArray($collector->getEventsByType());
    }

    public function testGetEventsByClass(): void
    {
        $collector = new DataMapperDataCollector(enabled: true);

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);
        $dataMapper->enableLineageCollection();

        $collector->setDataMapper($dataMapper);
        $collector->collect(
            new Request(),
            new Response()
        );

        $this->assertIsArray($collector->getEventsByClass());
    }
}
