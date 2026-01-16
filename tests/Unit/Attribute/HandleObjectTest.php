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

namespace Kassko\Bundle\DataMapperBundle\Tests\Unit\Attribute;

use Kassko\Bundle\DataMapperBundle\Attribute\HandleObject;
use PHPUnit\Framework\TestCase;

class HandleObjectTest extends TestCase
{
    public function testCanBeInstantiatedWithEmptyMapping(): void
    {
        $attribute = new HandleObject();

        $this->assertSame([], $attribute->mapping);
    }

    public function testCanBeInstantiatedWithMapping(): void
    {
        $mapping = [
            'request:barcode' => 'barcode',
            'header:Tenant-Id' => 'tenantId',
        ];

        $attribute = new HandleObject($mapping);

        $this->assertSame($mapping, $attribute->mapping);
    }

    public function testMappingWithContextTargets(): void
    {
        $mapping = [
            'request:barcode' => 'barcode',
            'header:feature-flags' => 'context:featureFlags',
            'header:x-request-id' => 'context:requestId',
        ];

        $attribute = new HandleObject($mapping);

        $this->assertSame($mapping, $attribute->mapping);
        $this->assertArrayHasKey('header:feature-flags', $attribute->mapping);
        $this->assertSame('context:featureFlags', $attribute->mapping['header:feature-flags']);
    }

    public function testIsTargetableOnParameters(): void
    {
        $reflection = new \ReflectionClass(HandleObject::class);
        $attributes = $reflection->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        
        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(\Attribute::TARGET_PARAMETER, $attributeInstance->flags);
    }
}
