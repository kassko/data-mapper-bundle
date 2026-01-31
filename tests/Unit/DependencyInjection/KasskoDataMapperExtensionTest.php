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

namespace Kassko\Bundle\DataMapperBundle\Tests\Unit\DependencyInjection;

use Kassko\Bundle\DataMapperBundle\DependencyInjection\KasskoDataMapperExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KasskoDataMapperExtensionTest extends TestCase
{
    private KasskoDataMapperExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new KasskoDataMapperExtension();
    }

    public function testGetAlias(): void
    {
        $this->assertEquals('kassko_data_mapper', $this->extension->getAlias());
    }

    public function testLoadWithDefaultConfiguration(): void
    {
        $container = new ContainerBuilder();

        $this->extension->load([], $container);

        // Check parameters are set
        $this->assertFalse($container->getParameter('kassko_data_mapper.enable_lineage_collection'));
        $this->assertTrue($container->getParameter('kassko_data_mapper.enable_profiler'));
        $this->assertFalse($container->getParameter('kassko_data_mapper.data_source_cache.enabled'));
        $this->assertNull($container->getParameter('kassko_data_mapper.data_source_cache.service'));
        $this->assertFalse($container->getParameter('kassko_data_mapper.mapping_cache.enabled'));
        $this->assertNull($container->getParameter('kassko_data_mapper.mapping_cache.service'));
        $this->assertFalse($container->getParameter('kassko_data_mapper.mapping_strategy.enabled'));
        $this->assertTrue($container->getParameter('kassko_data_mapper.logger.enabled'));
        $this->assertEquals('logger', $container->getParameter('kassko_data_mapper.logger.service'));
        $this->assertEquals('data_mapper', $container->getParameter('kassko_data_mapper.logger.channel'));
    }

    public function testLoadRegistersServices(): void
    {
        $container = new ContainerBuilder();

        $this->extension->load([], $container);

        // Check core services are registered
        $this->assertTrue($container->hasDefinition('kassko_data_mapper.data_mapper'));
        $this->assertTrue($container->hasDefinition('kassko_data_mapper.service_resolver'));
        $this->assertTrue($container->hasDefinition('kassko_data_mapper.service_resolver_factory'));
        $this->assertTrue($container->hasDefinition('kassko_data_mapper.data_mapper_configurator'));
    }

    public function testLoadRegistersDataCollector(): void
    {
        $container = new ContainerBuilder();

        $this->extension->load([], $container);

        $this->assertTrue($container->hasDefinition('kassko_data_mapper.data_collector'));
    }

    public function testLoadRegistersCommands(): void
    {
        $container = new ContainerBuilder();

        $this->extension->load([], $container);

        $this->assertTrue($container->hasDefinition('kassko_data_mapper.command.validate_class'));
        $this->assertTrue($container->hasDefinition('kassko_data_mapper.command.validate_metadata'));
    }

    public function testLoadWithLineageCollectionEnabled(): void
    {
        $container = new ContainerBuilder();

        $this->extension->load([
            ['enable_lineage_collection' => true],
        ], $container);

        $this->assertTrue($container->getParameter('kassko_data_mapper.enable_lineage_collection'));
    }

    public function testLoadWithProfilerDisabled(): void
    {
        $container = new ContainerBuilder();

        $this->extension->load([
            ['enable_profiler' => false],
        ], $container);

        $this->assertFalse($container->hasDefinition('kassko_data_mapper.data_collector'));
    }

    public function testLoadWithValidationPaths(): void
    {
        $container = new ContainerBuilder();

        $this->extension->load([
            [
                'validation' => [
                    'paths' => ['src/Entity'],
                    'namespaces' => ['App\\Entity'],
                ],
            ],
        ], $container);

        $this->assertEquals(['src/Entity'], $container->getParameter('kassko_data_mapper.validation.paths'));
        $this->assertEquals(['App\\Entity'], $container->getParameter('kassko_data_mapper.validation.namespaces'));
    }
}
