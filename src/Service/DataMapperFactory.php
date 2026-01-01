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

namespace Kassko\Bundle\DataMapperBundle\Service;

use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Enum\SensitiveLevel;
use Kassko\DataMapper\ServiceResolver;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Factory for creating DataMapper instances.
 *
 * This factory handles the conversion of configuration values (strings)
 * to proper types expected by DataMapper (enums, etc.).
 */
class DataMapperFactory
{
    /**
     * Create a new DataMapper instance.
     *
     * @param ServiceResolver $serviceResolver Service resolver for dependency injection
     * @param CacheInterface|null $cache PSR-16 cache interface
     * @param LoggerInterface|null $logger PSR-3 logger interface
     * @param array<string, string> $customHydrators Custom hydrators (key => service_id)
     * @param array<string, string> $sensitiveKeys Sensitive keys with level strings (key => 'show'|'mask'|'hide')
     * @param string $defaultSensitiveLevel Default sensitive level string ('show'|'mask'|'hide')
     */
    public function create(
        ServiceResolver $serviceResolver,
        ?CacheInterface $cache,
        ?LoggerInterface $logger,
        array $customHydrators,
        array $sensitiveKeys,
        string $defaultSensitiveLevel
    ): DataMapper {
        // Convert sensitive keys string values to SensitiveLevel enums
        $sensitiveKeysEnum = [];
        foreach ($sensitiveKeys as $key => $level) {
            $sensitiveKeysEnum[$key] = SensitiveLevel::from($level);
        }

        // Convert default sensitive level to enum
        $defaultLevelEnum = SensitiveLevel::from($defaultSensitiveLevel);

        return new DataMapper(
            $serviceResolver,
            $cache,
            $logger,
            $customHydrators,
            $sensitiveKeysEnum,
            $defaultLevelEnum
        );
    }
}
