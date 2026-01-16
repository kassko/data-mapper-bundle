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

namespace Kassko\Bundle\DataMapperBundle\Tests\Unit\DependencyInjection\Compiler;

use Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler\CustomObjectMapperPass;
use Kassko\Bundle\DataMapperBundle\Exception\DuplicateKeyException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class CustomObjectMapperPassTest extends TestCase
{
    private CustomObjectMapperPass $pass;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->pass = new CustomObjectMapperPass();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kassko_data_mapper.custom_object_mappers', []);
    }

    public function testMergesTaggedServicesWithEmptyConfig(): void
    {
        $definition = new Definition('App\ObjectMapper\ProductMapper');
        $definition->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'product']);
        $this->container->setDefinition('app.object_mapper.product', $definition);

        $this->pass->process($this->container);

        $mappers = $this->container->getParameter('kassko_data_mapper.custom_object_mappers');
        $this->assertSame(['product' => 'app.object_mapper.product'], $mappers);
    }

    public function testMergesTaggedServicesWithConfigMappers(): void
    {
        $this->container->setParameter('kassko_data_mapper.custom_object_mappers', [
            'order' => 'app.object_mapper.order',
        ]);

        $definition = new Definition('App\ObjectMapper\ProductMapper');
        $definition->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'product']);
        $this->container->setDefinition('app.object_mapper.product', $definition);

        $this->pass->process($this->container);

        $mappers = $this->container->getParameter('kassko_data_mapper.custom_object_mappers');
        $this->assertArrayHasKey('order', $mappers);
        $this->assertArrayHasKey('product', $mappers);
        $this->assertSame('app.object_mapper.order', $mappers['order']);
        $this->assertSame('app.object_mapper.product', $mappers['product']);
    }

    public function testThrowsExceptionOnDuplicateTaggedKeys(): void
    {
        $definition1 = new Definition('App\ObjectMapper\ProductMapper');
        $definition1->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'product']);
        $this->container->setDefinition('app.object_mapper.product', $definition1);

        $definition2 = new Definition('App\ObjectMapper\OtherProductMapper');
        $definition2->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'product']);
        $this->container->setDefinition('app.object_mapper.product_other', $definition2);

        $this->expectException(DuplicateKeyException::class);
        $this->expectExceptionMessage('Duplicate custom object mapper key "product"');

        $this->pass->process($this->container);
    }

    public function testThrowsExceptionOnTaggedKeyConflictWithConfig(): void
    {
        $this->container->setParameter('kassko_data_mapper.custom_object_mappers', [
            'product' => 'app.object_mapper.product.config',
        ]);

        $definition = new Definition('App\ObjectMapper\ProductMapper');
        $definition->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'product']);
        $this->container->setDefinition('app.object_mapper.product.tagged', $definition);

        $this->expectException(DuplicateKeyException::class);
        $this->expectExceptionMessage('conflicts with service "app.object_mapper.product.config" defined in semantic configuration');

        $this->pass->process($this->container);
    }

    public function testThrowsExceptionWhenKeyAttributeIsMissing(): void
    {
        $definition = new Definition('App\ObjectMapper\ProductMapper');
        $definition->addTag(CustomObjectMapperPass::TAG_NAME); // No key attribute
        $this->container->setDefinition('app.object_mapper.product', $definition);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must have a "key" attribute');

        $this->pass->process($this->container);
    }

    public function testHandlesMultipleTagsOnSameService(): void
    {
        $definition = new Definition('App\ObjectMapper\MultiMapper');
        $definition->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'product']);
        $definition->addTag(CustomObjectMapperPass::TAG_NAME, ['key' => 'variant']);
        $this->container->setDefinition('app.object_mapper.multi', $definition);

        $this->pass->process($this->container);

        $mappers = $this->container->getParameter('kassko_data_mapper.custom_object_mappers');
        $this->assertArrayHasKey('product', $mappers);
        $this->assertArrayHasKey('variant', $mappers);
        $this->assertSame('app.object_mapper.multi', $mappers['product']);
        $this->assertSame('app.object_mapper.multi', $mappers['variant']);
    }

    public function testNoTaggedServicesDoesNotModifyConfig(): void
    {
        $this->container->setParameter('kassko_data_mapper.custom_object_mappers', [
            'order' => 'app.object_mapper.order',
        ]);

        $this->pass->process($this->container);

        $mappers = $this->container->getParameter('kassko_data_mapper.custom_object_mappers');
        $this->assertSame(['order' => 'app.object_mapper.order'], $mappers);
    }
}
