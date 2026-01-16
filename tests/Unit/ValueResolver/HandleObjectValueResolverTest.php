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

namespace Kassko\Bundle\DataMapperBundle\Tests\Unit\ValueResolver;

use Kassko\Bundle\DataMapperBundle\Attribute\HandleObject;
use Kassko\Bundle\DataMapperBundle\ValueResolver\HandleObjectValueResolver;
use Kassko\DataMapper\ArrayServiceLocator;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\ServiceResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class HandleObjectValueResolverTest extends TestCase
{
    private ?DataMapper $dataMapper = null;
    private HandleObjectValueResolver $resolver;

    protected function setUp(): void
    {
        if (version_compare(\Symfony\Component\HttpKernel\Kernel::VERSION, '6.0.0', '<')) {
            $this->markTestSkipped('HandleObjectValueResolver is disabled by default in services.yaml to avoid issues with early container access in Symfony < 6.0. Enable it manually to run this test.');
        }
        
        // Use a real DataMapper instance since it's final and cannot be mocked
        $this->dataMapper = new DataMapper(new ServiceResolver());
        $this->resolver = new HandleObjectValueResolver(new ArrayServiceLocator(['data_mapper' => $this->dataMapper]));
    }

    protected function tearDown(): void
    {
        if (version_compare(\Symfony\Component\HttpKernel\Kernel::VERSION, '6.0.0', '<')) {
            return;
        }
        
        // Clear global state
        LoaderRegistry::clear();
        $this->dataMapper->clearContext();
    }

    public function testReturnsEmptyWhenNoAttribute(): void
    {
        $request = new Request();
        $argument = $this->createArgumentMetadata(TestProduct::class, []);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testReturnsEmptyWhenNoClassName(): void
    {
        $request = new Request();
        $argument = $this->createArgumentMetadataWithAttribute(null, new HandleObject([]));

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertEmpty($result);
    }

    public function testHydratesObjectFromRouteParameters(): void
    {
        $request = new Request(
            [],
            [],
            ['barcode' => '123456789'],
        );

        $attribute = new HandleObject(['request:barcode' => 'barcode']);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestProduct::class, $result[0]);
        $this->assertSame('123456789', $result[0]->barcode);
    }

    public function testHydratesObjectFromQueryParameters(): void
    {
        $request = new Request(['search' => 'foo']);

        $attribute = new HandleObject(['query:search' => 'searchTerm']);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestProduct::class, $result[0]);
        $this->assertSame('foo', $result[0]->searchTerm);
    }

    public function testHydratesObjectFromHeaders(): void
    {
        $request = new Request();
        $request->headers->set('Tenant-Id', 'tenant-123');

        $attribute = new HandleObject(['header:Tenant-Id' => 'tenantId']);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertInstanceOf(TestProduct::class, $result[0]);
        $this->assertSame('tenant-123', $result[0]->tenantId);
    }

    public function testHeadersAreCaseInsensitive(): void
    {
        $request = new Request();
        $request->headers->set('tenant-id', 'tenant-123');

        $attribute = new HandleObject(['header:TENANT-ID' => 'tenantId']);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertSame('tenant-123', $result[0]->tenantId);
    }

    public function testHydratesObjectFromJsonBody(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            json_encode(['name' => 'Product Name', 'price' => '100'])
        );

        $attribute = new HandleObject([
            'body:name' => 'productName',
            'body:price' => 'productPrice',
        ]);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertSame('Product Name', $result[0]->productName);
        $this->assertSame('100', $result[0]->productPrice);
    }

    public function testAddsContextValues(): void
    {
        $request = new Request();
        $request->headers->set('feature-flags', 'fastSearch,debug');
        $request->headers->set('x-request-id', 'req-123');

        $attribute = new HandleObject([
            'header:feature-flags' => 'context:featureFlags',
            'header:x-request-id' => 'context:requestId',
        ]);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertTrue($this->dataMapper->hasContext('featureFlags'));
        $this->assertTrue($this->dataMapper->hasContext('requestId'));
        $this->assertSame('fastSearch,debug', $this->dataMapper->getContext('featureFlags'));
        $this->assertSame('req-123', $this->dataMapper->getContext('requestId'));
    }

    public function testOptionalHeadersAreSkippedWhenMissing(): void
    {
        $request = new Request();
        // X-Request-Id is not set (optional header with X- prefix)

        $attribute = new HandleObject([
            'header:X-Request-Id' => 'context:requestId',
        ]);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        // Context value should not be set for missing optional header
        $this->assertFalse($this->dataMapper->hasContext('requestId'));
    }

    public function testMixedSources(): void
    {
        $request = new Request(
            ['page' => '1'],
            [],
            ['id' => '42'],
            [],
            [],
            [],
            json_encode(['name' => 'Product'])
        );
        $request->headers->set('Tenant-Id', 'tenant-123');
        $request->headers->set('feature-flags', 'flag1,flag2');

        $attribute = new HandleObject([
            'request:id' => 'productId',
            'query:page' => 'pageNumber',
            'body:name' => 'productName',
            'header:Tenant-Id' => 'tenantId',
            'header:feature-flags' => 'context:featureFlags',
        ]);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $product = $result[0];
        $this->assertSame('42', $product->productId);
        $this->assertSame('1', $product->pageNumber);
        $this->assertSame('Product', $product->productName);
        $this->assertSame('tenant-123', $product->tenantId);
        $this->assertSame('flag1,flag2', $this->dataMapper->getContext('featureFlags'));
    }

    public function testHandlesEmptyJsonBody(): void
    {
        $request = new Request();

        $attribute = new HandleObject(['body:name' => 'nullableName']);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        // nullableName should be null since body field is not present
        $this->assertNull($result[0]->nullableName);
    }

    public function testHandlesInvalidJsonBody(): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            'not valid json'
        );

        $attribute = new HandleObject(['body:name' => 'nullableName']);
        $argument = $this->createArgumentMetadataWithAttribute(TestProduct::class, $attribute);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertNull($result[0]->nullableName);
    }

    /**
     * @param array<HandleObject> $attributes
     */
    private function createArgumentMetadata(string $type, array $attributes): ArgumentMetadata
    {
        return new ArgumentMetadata(
            'product',
            $type,
            false,
            false,
            null,
            false,
            $attributes
        );
    }

    private function createArgumentMetadataWithAttribute(?string $type, HandleObject $attribute): ArgumentMetadata
    {
        return new ArgumentMetadata(
            'product',
            $type,
            false,
            false,
            null,
            false,
            [$attribute]
        );
    }
}

/**
 * Test class for hydration.
 */
class TestProduct
{
    public string $barcode = '';
    public string $tenantId = '';
    public string $searchTerm = '';
    public string $productName = '';
    public string $productPrice = '';
    public string $productId = '';
    public string $pageNumber = '';
    public ?string $nullableName = null;
}
