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
 * Compiler pass that collects tagged custom hydrator services.
 *
 * This pass processes services tagged with 'kassko_data_mapper.custom_hydrator'
 * and merges them with hydrators defined in the semantic configuration.
 * It also validates that all hydrator keys are unique.
 */
final class CustomHydratorPass implements CompilerPassInterface
{
    public const TAG_NAME = 'kassko_data_mapper.custom_hydrator';

    public function process(ContainerBuilder $container): void
    {
        // Get hydrators from semantic configuration (may not exist if extension not loaded)
        $configHydrators = [];
        if ($container->hasParameter('kassko_data_mapper.custom_hydrators')) {
            $configHydrators = $container->getParameter('kassko_data_mapper.custom_hydrators');
            if (!is_array($configHydrators)) {
                $configHydrators = [];
            }
        }

        // Get hydrators from tagged services
        $taggedHydrators = [];
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
                if (isset($taggedHydrators[$key])) {
                    throw new DuplicateKeyException(sprintf(
                        'Duplicate custom hydrator key "%s": service "%s" conflicts with service "%s". '
                        . 'Each custom hydrator must have a unique key.',
                        $key,
                        $serviceId,
                        $taggedHydrators[$key]
                    ));
                }

                // Check for duplicate key between tagged and config
                if (isset($configHydrators[$key])) {
                    throw new DuplicateKeyException(sprintf(
                        'Duplicate custom hydrator key "%s": tagged service "%s" conflicts with service "%s" '
                        . 'defined in semantic configuration. Each custom hydrator must have a unique key.',
                        $key,
                        $serviceId,
                        $configHydrators[$key]
                    ));
                }

                $taggedHydrators[$key] = $serviceId;
            }
        }

        // Merge tagged hydrators with config hydrators
        // Tagged services take precedence (but we already throw on duplicates, so order doesn't matter)
        $allHydrators = array_merge($configHydrators, $taggedHydrators);

        // Update the parameter with the merged list
        $container->setParameter('kassko_data_mapper.custom_hydrators', $allHydrators);
    }
}
