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

namespace Kassko\Bundle\DataMapperBundle\DependencyInjection\Compiler;

use Kassko\Bundle\DataMapperBundle\Exception\DuplicateKeyException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that collects tagged custom object mapper services.
 *
 * This pass processes services tagged with 'kassko_data_mapper.custom_object_mapper'
 * and merges them with object mappers defined in the semantic configuration.
 * It also validates that all object mapper keys are unique.
 */
final class CustomObjectMapperPass implements CompilerPassInterface
{
    public const TAG_NAME = 'kassko_data_mapper.custom_object_mapper';

    public function process(ContainerBuilder $container): void
    {
        // Get object mappers from semantic configuration
        $configObjectMappers = $container->getParameter('kassko_data_mapper.custom_object_mappers');
        if (!is_array($configObjectMappers)) {
            $configObjectMappers = [];
        }

        // Get object mappers from tagged services
        $taggedObjectMappers = [];
        $taggedServices = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $key = $tag['key'] ?? null;

                if ($key === null) {
                    throw new \InvalidArgumentException(sprintf(
                        'Service "%s" tagged with "%s" must have a "key" attribute.',
                        $serviceId,
                        self::TAG_NAME
                    ));
                }

                // Check for duplicate key in tagged services
                if (isset($taggedObjectMappers[$key])) {
                    throw new DuplicateKeyException(sprintf(
                        'Duplicate custom object mapper key "%s": service "%s" conflicts with service "%s". '
                        . 'Each custom object mapper must have a unique key.',
                        $key,
                        $serviceId,
                        $taggedObjectMappers[$key]
                    ));
                }

                // Check for duplicate key between tagged and config
                if (isset($configObjectMappers[$key])) {
                    throw new DuplicateKeyException(sprintf(
                        'Duplicate custom object mapper key "%s": tagged service "%s" conflicts with service "%s" '
                        . 'defined in semantic configuration. Each custom object mapper must have a unique key.',
                        $key,
                        $serviceId,
                        $configObjectMappers[$key]
                    ));
                }

                $taggedObjectMappers[$key] = $serviceId;
            }
        }

        // Merge tagged object mappers with config object mappers
        $allObjectMappers = array_merge($configObjectMappers, $taggedObjectMappers);

        // Update the parameter with the merged list
        $container->setParameter('kassko_data_mapper.custom_object_mappers', $allObjectMappers);
    }
}
