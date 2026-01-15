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

namespace Kassko\Bundle\DataMapperBundle\ValueResolver;

use Kassko\Bundle\DataMapperBundle\Attribute\HandleObject;
use Kassko\DataMapper\DataMapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Value resolver for HandleObject attribute.
 *
 * This resolver hydrates controller action parameters annotated with #[HandleObject]
 * by extracting data from the request and using DataMapper for hydration.
 *
 * Sources supported:
 * - request: Route parameters
 * - query: Query string parameters
 * - header: HTTP headers (case-insensitive)
 * - body: JSON request body fields
 *
 * Target types:
 * - Property name: Value is passed to the object hydration
 * - context:key: Value is added to the hydration context
 */
final class HandleObjectValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly DataMapper $dataMapper,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $this->getHandleObjectAttribute($argument);

        if ($attribute === null) {
            return [];
        }

        $className = $argument->getType();

        if ($className === null || !class_exists($className)) {
            return [];
        }

        // Extract values from request and separate into properties and context
        [$properties, $context] = $this->extractValues($request, $attribute->mapping);

        // Add context values to DataMapper
        foreach ($context as $key => $value) {
            $this->dataMapper->addToContext($key, $value);
        }

        // Hydrate the object
        $hydrator = $this->dataMapper->getHydrator();
        $object = $hydrator->hydrate($className, $properties);

        yield $object;
    }

    /**
     * Get the HandleObject attribute from the argument metadata.
     */
    private function getHandleObjectAttribute(ArgumentMetadata $argument): ?HandleObject
    {
        foreach ($argument->getAttributes(HandleObject::class) as $attribute) {
            return $attribute;
        }

        return null;
    }

    /**
     * Extract values from the request based on the mapping configuration.
     *
     * @param Request $request The HTTP request
     * @param array<string, string> $mapping Source to target mapping
     * @return array{0: array<string, mixed>, 1: array<string, mixed>} [properties, context]
     */
    private function extractValues(Request $request, array $mapping): array
    {
        $properties = [];
        $context = [];

        // Parse JSON body once if needed
        $bodyData = null;
        foreach ($mapping as $source => $target) {
            if (str_starts_with($source, 'body:')) {
                $bodyData = $this->parseJsonBody($request);
                break;
            }
        }

        foreach ($mapping as $source => $target) {
            $value = $this->extractValueFromSource($request, $source, $bodyData);

            if ($value === null) {
                // Check if source is optional (X- prefix for headers)
                if ($this->isOptionalSource($source)) {
                    continue;
                }
            }

            // Determine if target is context or property
            if (str_starts_with($target, 'context:')) {
                $contextKey = substr($target, 8);
                $context[$contextKey] = $value;
            } else {
                $properties[$target] = $value;
            }
        }

        return [$properties, $context];
    }

    /**
     * Extract a value from the request based on the source specification.
     *
     * @param Request $request The HTTP request
     * @param string $source Source specification (e.g., 'request:barcode', 'header:Tenant-Id')
     * @param array<string, mixed>|null $bodyData Parsed JSON body data
     * @return mixed The extracted value or null if not found
     */
    private function extractValueFromSource(Request $request, string $source, ?array $bodyData): mixed
    {
        $parts = explode(':', $source, 2);
        if (count($parts) !== 2) {
            return null;
        }

        [$type, $key] = $parts;

        return match ($type) {
            'request' => $request->attributes->get($key),
            'query' => $request->query->get($key),
            'header' => $this->getHeader($request, $key),
            'body' => $bodyData[$key] ?? null,
            default => null,
        };
    }

    /**
     * Get a header value from the request (case-insensitive).
     *
     * @param Request $request The HTTP request
     * @param string $headerName The header name (case-insensitive)
     * @return string|null The header value or null if not found
     */
    private function getHeader(Request $request, string $headerName): ?string
    {
        // Symfony's HeaderBag is case-insensitive
        return $request->headers->get($headerName);
    }

    /**
     * Check if a source is optional (X- prefixed headers are optional).
     *
     * @param string $source The source specification
     * @return bool True if the source is optional
     */
    private function isOptionalSource(string $source): bool
    {
        if (!str_starts_with($source, 'header:')) {
            return false;
        }

        $headerName = substr($source, 7);
        
        // Headers prefixed with X- (case-insensitive) are optional
        return str_starts_with(strtolower($headerName), 'x-');
    }

    /**
     * Parse the JSON body from the request.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The parsed JSON data
     */
    private function parseJsonBody(Request $request): array
    {
        $content = $request->getContent();

        if (empty($content)) {
            return [];
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            return is_array($data) ? $data : [];
        } catch (\JsonException) {
            return [];
        }
    }
}
