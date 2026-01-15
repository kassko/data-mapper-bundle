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

namespace Kassko\Bundle\DataMapperBundle\Attribute;

/**
 * HandleObject attribute for controller action parameters.
 *
 * This attribute allows hydrating objects from request data using DataMapper.
 * It supports mapping values from various request sources (path, query, headers, body)
 * to object properties or hydration context.
 *
 * Mapping format:
 * - 'request:param' => 'propertyName'     Maps route parameter to object property
 * - 'query:param' => 'propertyName'       Maps query string parameter to object property
 * - 'header:Header-Name' => 'propertyName' Maps header (case-insensitive) to object property
 * - 'body:field' => 'propertyName'        Maps JSON body field to object property
 * - 'source:key' => 'context:contextKey'  Maps value to hydration context instead of property
 *
 * Optional headers (prefixed with X-) are not required.
 *
 * Example:
 * ```php
 * #[Route('/products/{barcode}', name: 'api_products_get', methods: ['GET'])]
 * public function getProduct(
 *     #[HandleObject([
 *         'request:barcode' => 'barcode',
 *         'header:Tenant-Id' => 'tenantId',
 *         'header:feature-flags' => 'context:featureFlags',
 *         'header:x-request-id' => 'context:requestId',
 *     ])]
 *     Product $product,
 * ): JsonResponse {
 *     // $product is hydrated with barcode and tenantId properties
 *     // context contains featureFlags and requestId
 * }
 * ```
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class HandleObject
{
    /**
     * @param array<string, string> $mapping Source to target mapping
     */
    public function __construct(
        public readonly array $mapping = [],
    ) {
    }
}
