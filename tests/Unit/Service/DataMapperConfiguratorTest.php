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

use Kassko\Bundle\DataMapperBundle\DataCollector\DataMapperDataCollector;
use Kassko\Bundle\DataMapperBundle\Service\DataMapperConfigurator;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ServiceResolver;
use PHPUnit\Framework\TestCase;

class DataMapperConfiguratorTest extends TestCase
{
    public function testConfigureWithLineageCollectionEnabled(): void
    {
        $dataCollector = $this->createMock(DataMapperDataCollector::class);
        $dataCollector->expects($this->once())
            ->method('setDataMapper');

        $configurator = new DataMapperConfigurator(
            enableLineageCollection: true,
            enableCascadeCollection: false,
            dataCollector: $dataCollector
        );

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);

        $configurator->configure($dataMapper);

        // The lineage collector should be enabled
        $this->assertTrue($dataMapper->getLineageCollector()->isEnabled());
    }

    public function testConfigureWithLineageCollectionDisabled(): void
    {
        $dataCollector = $this->createMock(DataMapperDataCollector::class);

        $configurator = new DataMapperConfigurator(
            enableLineageCollection: false,
            enableCascadeCollection: false,
            dataCollector: $dataCollector
        );

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);

        $configurator->configure($dataMapper);

        // The lineage collector should remain disabled
        $this->assertFalse($dataMapper->getLineageCollector()->isEnabled());
    }

    public function testConfigureWithoutDataCollector(): void
    {
        $configurator = new DataMapperConfigurator(
            enableLineageCollection: true,
            enableCascadeCollection: false,
            dataCollector: null
        );

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);

        // Should not throw an exception
        $configurator->configure($dataMapper);

        $this->assertTrue($dataMapper->getLineageCollector()->isEnabled());
    }

    public function testConfigureWithCascadeCollectionEnabled(): void
    {
        // Skip if enableCascadeCollection method is not available in DataMapper
        if (!method_exists(DataMapper::class, 'enableCascadeCollection')) {
            $this->markTestSkipped('enableCascadeCollection() not yet implemented in DataMapper');
        }

        $configurator = new DataMapperConfigurator(
            enableLineageCollection: false,
            enableCascadeCollection: true,
            dataCollector: null
        );

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);

        $configurator->configure($dataMapper);

        // The cascade collector should be enabled
        $this->assertTrue($dataMapper->getCascadeCollector()->isEnabled());
    }

    public function testConfigureWithCascadeCollectionDisabled(): void
    {
        // Skip if getCascadeCollector method is not available in DataMapper
        if (!method_exists(DataMapper::class, 'getCascadeCollector')) {
            $this->markTestSkipped('getCascadeCollector() not yet implemented in DataMapper');
        }

        $configurator = new DataMapperConfigurator(
            enableLineageCollection: false,
            enableCascadeCollection: false,
            dataCollector: null
        );

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);

        $configurator->configure($dataMapper);

        // The cascade collector should remain disabled
        $this->assertFalse($dataMapper->getCascadeCollector()->isEnabled());
    }

    public function testConfigureWithBothCollectionsEnabled(): void
    {
        // Skip if enableCascadeCollection method is not available in DataMapper
        if (!method_exists(DataMapper::class, 'enableCascadeCollection')) {
            $this->markTestSkipped('enableCascadeCollection() not yet implemented in DataMapper');
        }

        $configurator = new DataMapperConfigurator(
            enableLineageCollection: true,
            enableCascadeCollection: true,
            dataCollector: null
        );

        $serviceResolver = new ServiceResolver();
        $dataMapper = new DataMapper($serviceResolver);

        $configurator->configure($dataMapper);

        // Both collectors should be enabled
        $this->assertTrue($dataMapper->getLineageCollector()->isEnabled());
        $this->assertTrue($dataMapper->getCascadeCollector()->isEnabled());
    }
}
