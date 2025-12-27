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

namespace Kassko\Bundle\DataMapperBundle\Tests\Integration;

use Kassko\Bundle\DataMapperBundle\DataCollector\DataMapperDataCollector;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\ServiceResolver;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the bundle with a real Symfony kernel.
 */
class BundleIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        // Restore exception handler after Symfony kernel shutdown
        restore_exception_handler();

        // Clean up temp cache dirs
        $cacheDir = sys_get_temp_dir() . '/kassko_data_mapper_bundle';
        if (is_dir($cacheDir)) {
            // Simple cleanup - in real scenario use Filesystem component
            @shell_exec("rm -rf " . escapeshellarg($cacheDir));
        }
    }

    public function testBundleBootsSuccessfully(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $this->assertTrue($kernel->getContainer()->has('kassko_data_mapper.data_mapper'));

        $kernel->shutdown();
    }

    public function testDataMapperServiceIsAvailable(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $dataMapper = $kernel->getContainer()->get('kassko_data_mapper.data_mapper');

        $this->assertInstanceOf(DataMapper::class, $dataMapper);

        $kernel->shutdown();
    }

    public function testServiceResolverIsConfigured(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $dataMapper = $kernel->getContainer()->get('kassko_data_mapper.data_mapper');

        $this->assertInstanceOf(ServiceResolver::class, $dataMapper->getServiceResolver());

        $kernel->shutdown();
    }

    public function testLineageCollectionCanBeEnabled(): void
    {
        $kernel = new TestKernel([
            'enable_lineage_collection' => true,
        ]);
        $kernel->boot();

        $dataMapper = $kernel->getContainer()->get('kassko_data_mapper.data_mapper');

        $this->assertTrue($dataMapper->getLineageCollector()->isEnabled());

        $kernel->shutdown();
    }

    public function testLineageCollectionDisabledByDefault(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $dataMapper = $kernel->getContainer()->get('kassko_data_mapper.data_mapper');

        $this->assertFalse($dataMapper->getLineageCollector()->isEnabled());

        $kernel->shutdown();
    }

    public function testDataCollectorIsRegistered(): void
    {
        $kernel = new TestKernel([
            'enable_profiler' => true,
        ]);
        $kernel->boot();

        $container = $kernel->getContainer();

        $this->assertTrue($container->has('kassko_data_mapper.data_collector'));

        $dataCollector = $container->get('kassko_data_mapper.data_collector');
        $this->assertInstanceOf(DataMapperDataCollector::class, $dataCollector);

        $kernel->shutdown();
    }

    public function testContextCanBeManaged(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $dataMapper = $kernel->getContainer()->get('kassko_data_mapper.data_mapper');

        // Add a value to context
        $dataMapper->addToContext('test_key', 'test_value');

        // Verify it's retrievable
        $this->assertTrue($dataMapper->hasContext('test_key'));
        $this->assertEquals('test_value', $dataMapper->getContext('test_key'));

        // Clear context
        $dataMapper->clearContext();
        $this->assertFalse($dataMapper->hasContext('test_key'));

        $kernel->shutdown();
    }

    public function testAddManyToContext(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $dataMapper = $kernel->getContainer()->get('kassko_data_mapper.data_mapper');

        $dataMapper->addManyToContext([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertEquals('value1', $dataMapper->getContext('key1'));
        $this->assertEquals('value2', $dataMapper->getContext('key2'));

        $kernel->shutdown();
    }
}
