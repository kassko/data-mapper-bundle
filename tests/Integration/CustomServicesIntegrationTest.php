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

use Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler\CustomHydratorPass;
use Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler\CustomObjectMapperPass;
use Kassko\Bundle\DataMapperBundle\Exception\DuplicateKeyException;
use Kassko\Bundle\DataMapperBundle\ValueResolver\HandleObjectValueResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Integration tests for custom hydrators, object mappers, and value resolver.
 */
class CustomServicesIntegrationTest extends TestCase
{
    protected function tearDown(): void
    {
        restore_exception_handler();

        $cacheDir = sys_get_temp_dir() . '/kassko_data_mapper_bundle';
        if (is_dir($cacheDir)) {
            @shell_exec("rm -rf " . escapeshellarg($cacheDir));
        }
    }

    public function testValueResolverIsRegistered(): void
    {
        $kernel = new TestKernel();
        $kernel->boot();

        $container = $kernel->getContainer();

        $this->assertTrue($container->has('kassko_data_mapper.value_resolver.handle_object'));

        $kernel->shutdown();
    }

    public function testCustomHydratorsFromConfigAreAvailable(): void
    {
        $kernel = new TestKernel([
            'custom_hydrators' => [
                'datetime' => 'app.hydrator.datetime',
            ],
        ]);
        $kernel->boot();

        $container = $kernel->getContainer();
        
        $hydrators = $container->getParameter('kassko_data_mapper.custom_hydrators');
        $this->assertArrayHasKey('datetime', $hydrators);
        $this->assertSame('app.hydrator.datetime', $hydrators['datetime']);

        $kernel->shutdown();
    }

    public function testCustomObjectMappersFromConfigAreAvailable(): void
    {
        $kernel = new TestKernel([
            'custom_object_mappers' => [
                'product' => 'app.object_mapper.product',
            ],
        ]);
        $kernel->boot();

        $container = $kernel->getContainer();
        
        $mappers = $container->getParameter('kassko_data_mapper.custom_object_mappers');
        $this->assertArrayHasKey('product', $mappers);
        $this->assertSame('app.object_mapper.product', $mappers['product']);

        $kernel->shutdown();
    }

    public function testTaggedCustomHydratorsAreMerged(): void
    {        
        $kernel = new TestKernelWithTaggedHydrator([
            'custom_hydrators' => [
                'money' => 'app.hydrator.money',
            ],
        ]);
        $kernel->boot();

        $container = $kernel->getContainer();
        
        $hydrators = $container->getParameter('kassko_data_mapper.custom_hydrators');
        
        // Config hydrator
        $this->assertArrayHasKey('money', $hydrators);
        $this->assertSame('app.hydrator.money', $hydrators['money']);
        
        // Tagged hydrator
        $this->assertArrayHasKey('datetime', $hydrators);
        $this->assertSame('app.hydrator.datetime_service', $hydrators['datetime']);

        $kernel->shutdown();
    }

    public function testTaggedCustomObjectMappersAreMerged(): void
    {      
        $kernel = new TestKernelWithTaggedObjectMapper([
            'custom_object_mappers' => [
                'order' => 'app.object_mapper.order',
            ],
        ]);
        $kernel->boot();

        $container = $kernel->getContainer();
        
        $mappers = $container->getParameter('kassko_data_mapper.custom_object_mappers');
        
        // Config mapper
        $this->assertArrayHasKey('order', $mappers);
        $this->assertSame('app.object_mapper.order', $mappers['order']);
        
        // Tagged mapper
        $this->assertArrayHasKey('product', $mappers);
        $this->assertSame('app.object_mapper.product_service', $mappers['product']);

        $kernel->shutdown();
    }

    public function testDuplicateHydratorKeyThrowsException(): void
    {      
        $this->expectException(DuplicateKeyException::class);
        $this->expectExceptionMessage('Duplicate custom hydrator key "datetime"');

        $kernel = new TestKernelWithDuplicateHydratorKey([
            'custom_hydrators' => [
                'datetime' => 'app.hydrator.datetime.config',
            ],
        ]);
        $kernel->boot();
    }

    public function testDuplicateObjectMapperKeyThrowsException(): void
    {        
        $this->expectException(DuplicateKeyException::class);
        $this->expectExceptionMessage('Duplicate custom object mapper key "product"');

        $kernel = new TestKernelWithDuplicateObjectMapperKey([
            'custom_object_mappers' => [
                'product' => 'app.object_mapper.product.config',
            ],
        ]);
        $kernel->boot();
    }
}

/**
 * Test kernel with a tagged custom hydrator.
 */
class TestKernelWithTaggedHydrator extends TestKernel
{
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $definition = new Definition(\stdClass::class);
                $definition->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'datetime']);
                $container->setDefinition('app.hydrator.datetime_service', $definition);
            }
        }, \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
    }
}

/**
 * Test kernel with a tagged custom object mapper.
 */
class TestKernelWithTaggedObjectMapper extends TestKernel
{
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $definition = new Definition(\stdClass::class);
                $definition->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'product']);
                $container->setDefinition('app.object_mapper.product_service', $definition);
            }
        }, \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
    }
}

/**
 * Test kernel with duplicate hydrator key.
 */
class TestKernelWithDuplicateHydratorKey extends TestKernel
{
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $definition = new Definition(\stdClass::class);
                $definition->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'datetime']);
                $container->setDefinition('app.hydrator.datetime.tagged', $definition);
            }
        }, \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
    }
}

/**
 * Test kernel with duplicate object mapper key.
 */
class TestKernelWithDuplicateObjectMapperKey extends TestKernel
{
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new class implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                $definition = new Definition(\stdClass::class);
                $definition->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'product']);
                $container->setDefinition('app.object_mapper.product.tagged', $definition);
            }
        }, \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
    }
}
