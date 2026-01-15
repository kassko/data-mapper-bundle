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

use Kassko\Bundle\DataMapperBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    private Configuration $configuration;
    private Processor $processor;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->processor = new Processor();
    }

    public function testDefaultConfiguration(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            []
        );

        $this->assertFalse($config['enable_lineage_collection']);
        $this->assertFalse($config['enable_cascade_collection']);
        $this->assertTrue($config['enable_profiler']);
        $this->assertFalse($config['cache']['enabled']);
        $this->assertNull($config['cache']['service']);
        $this->assertTrue($config['logger']['enabled']);
        $this->assertEquals('logger', $config['logger']['service']);
        $this->assertEquals('data_mapper', $config['logger']['channel']);
        $this->assertEmpty($config['validation']['paths']);
        $this->assertEmpty($config['validation']['namespaces']);
        $this->assertEmpty($config['custom_hydrators']);
        $this->assertEmpty($config['custom_object_mappers']);
        $this->assertEmpty($config['sensitive_keys']);
        $this->assertEquals('show', $config['default_sensitive_level']);
    }

    public function testEnableLineageCollection(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                ['enable_lineage_collection' => true],
            ]
        );

        $this->assertTrue($config['enable_lineage_collection']);
    }

    public function testDisableProfiler(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                ['enable_profiler' => false],
            ]
        );

        $this->assertFalse($config['enable_profiler']);
    }

    public function testCacheConfiguration(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'cache' => [
                        'enabled' => true,
                        'service' => 'cache.app',
                    ],
                ],
            ]
        );

        $this->assertTrue($config['cache']['enabled']);
        $this->assertEquals('cache.app', $config['cache']['service']);
    }

    public function testLoggerConfiguration(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'logger' => [
                        'enabled' => true,
                        'service' => 'monolog.logger',
                        'channel' => 'custom_channel',
                    ],
                ],
            ]
        );

        $this->assertTrue($config['logger']['enabled']);
        $this->assertEquals('monolog.logger', $config['logger']['service']);
        $this->assertEquals('custom_channel', $config['logger']['channel']);
    }

    public function testValidationConfiguration(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'validation' => [
                        'paths' => ['src/Entity', 'src/Model'],
                        'namespaces' => ['App\\Entity', 'App\\Model'],
                    ],
                ],
            ]
        );

        $this->assertEquals(['src/Entity', 'src/Model'], $config['validation']['paths']);
        $this->assertEquals(['App\\Entity', 'App\\Model'], $config['validation']['namespaces']);
    }

    public function testMergeConfigurations(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                ['enable_lineage_collection' => false],
                ['enable_lineage_collection' => true],
            ]
        );

        // Later configuration should override earlier
        $this->assertTrue($config['enable_lineage_collection']);
    }

    public function testEnableCascadeCollection(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                ['enable_cascade_collection' => true],
            ]
        );

        $this->assertTrue($config['enable_cascade_collection']);
    }

    public function testCustomHydratorsConfiguration(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'custom_hydrators' => [
                        'datetime' => 'app.hydrator.datetime',
                        'money' => 'app.hydrator.money',
                    ],
                ],
            ]
        );

        $this->assertEquals([
            'datetime' => 'app.hydrator.datetime',
            'money' => 'app.hydrator.money',
        ], $config['custom_hydrators']);
    }

    public function testSensitiveKeysConfiguration(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'sensitive_keys' => [
                        'password' => 'hide',
                        'api_key' => 'mask',
                        'email' => 'show',
                    ],
                ],
            ]
        );

        $this->assertEquals([
            'password' => 'hide',
            'api_key' => 'mask',
            'email' => 'show',
        ], $config['sensitive_keys']);
    }

    public function testDefaultSensitiveLevelConfiguration(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                ['default_sensitive_level' => 'mask'],
            ]
        );

        $this->assertEquals('mask', $config['default_sensitive_level']);
    }

    public function testDefaultSensitiveLevelHide(): void
    {
        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                ['default_sensitive_level' => 'hide'],
            ]
        );

        $this->assertEquals('hide', $config['default_sensitive_level']);
    }
}
