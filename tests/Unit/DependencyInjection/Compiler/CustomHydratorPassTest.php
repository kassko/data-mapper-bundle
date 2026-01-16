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

use Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler\CustomHydratorPass;
use Kassko\Bundle\DataMapperBundle\Exception\DuplicateKeyException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class CustomHydratorPassTest extends TestCase
{
    private CustomHydratorPass $pass;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->pass = new CustomHydratorPass();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kassko_data_mapper.custom_hydrators', []);
    }

    public function testMergesTaggedServicesWithEmptyConfig(): void
    {
        $definition = new Definition('App\Hydrator\DateTimeHydrator');
        $definition->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'datetime']);
        $this->container->setDefinition('app.hydrator.datetime', $definition);

        $this->pass->process($this->container);

        $hydrators = $this->container->getParameter('kassko_data_mapper.custom_hydrators');
        $this->assertSame(['datetime' => 'app.hydrator.datetime'], $hydrators);
    }

    public function testMergesTaggedServicesWithConfigHydrators(): void
    {
        $this->container->setParameter('kassko_data_mapper.custom_hydrators', [
            'money' => 'app.hydrator.money',
        ]);

        $definition = new Definition('App\Hydrator\DateTimeHydrator');
        $definition->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'datetime']);
        $this->container->setDefinition('app.hydrator.datetime', $definition);

        $this->pass->process($this->container);

        $hydrators = $this->container->getParameter('kassko_data_mapper.custom_hydrators');
        $this->assertArrayHasKey('money', $hydrators);
        $this->assertArrayHasKey('datetime', $hydrators);
        $this->assertSame('app.hydrator.money', $hydrators['money']);
        $this->assertSame('app.hydrator.datetime', $hydrators['datetime']);
    }

    public function testThrowsExceptionOnDuplicateTaggedKeys(): void
    {
        $definition1 = new Definition('App\Hydrator\DateTimeHydrator');
        $definition1->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'datetime']);
        $this->container->setDefinition('app.hydrator.datetime', $definition1);

        $definition2 = new Definition('App\Hydrator\OtherDateTimeHydrator');
        $definition2->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'datetime']);
        $this->container->setDefinition('app.hydrator.datetime_other', $definition2);

        $this->expectException(DuplicateKeyException::class);
        $this->expectExceptionMessage('Duplicate custom hydrator key "datetime"');

        $this->pass->process($this->container);
    }

    public function testThrowsExceptionOnTaggedKeyConflictWithConfig(): void
    {
        $this->container->setParameter('kassko_data_mapper.custom_hydrators', [
            'datetime' => 'app.hydrator.datetime.config',
        ]);

        $definition = new Definition('App\Hydrator\DateTimeHydrator');
        $definition->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'datetime']);
        $this->container->setDefinition('app.hydrator.datetime.tagged', $definition);

        $this->expectException(DuplicateKeyException::class);
        $this->expectExceptionMessage('conflicts with service "app.hydrator.datetime.config" defined in semantic configuration');

        $this->pass->process($this->container);
    }

    public function testThrowsExceptionWhenKeyAttributeIsMissing(): void
    {
        $definition = new Definition('App\Hydrator\DateTimeHydrator');
        $definition->addTag(CustomHydratorPass::TAG_NAME); // No key attribute
        $this->container->setDefinition('app.hydrator.datetime', $definition);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must have a "key" attribute');

        $this->pass->process($this->container);
    }

    public function testHandlesMultipleTagsOnSameService(): void
    {
        $definition = new Definition('App\Hydrator\MultiHydrator');
        $definition->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'datetime']);
        $definition->addTag(CustomHydratorPass::TAG_NAME, ['key' => 'date']);
        $this->container->setDefinition('app.hydrator.multi', $definition);

        $this->pass->process($this->container);

        $hydrators = $this->container->getParameter('kassko_data_mapper.custom_hydrators');
        $this->assertArrayHasKey('datetime', $hydrators);
        $this->assertArrayHasKey('date', $hydrators);
        $this->assertSame('app.hydrator.multi', $hydrators['datetime']);
        $this->assertSame('app.hydrator.multi', $hydrators['date']);
    }

    public function testNoTaggedServicesDoesNotModifyConfig(): void
    {
        $this->container->setParameter('kassko_data_mapper.custom_hydrators', [
            'money' => 'app.hydrator.money',
        ]);

        $this->pass->process($this->container);

        $hydrators = $this->container->getParameter('kassko_data_mapper.custom_hydrators');
        $this->assertSame(['money' => 'app.hydrator.money'], $hydrators);
    }
}
