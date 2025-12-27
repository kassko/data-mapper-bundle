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

use Kassko\Bundle\DataMapperBundle\DataMapperBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * A minimal Symfony kernel for integration testing.
 */
class TestKernel extends Kernel
{
    private array $bundleConfig;

    public function __construct(array $bundleConfig = [])
    {
        $this->bundleConfig = $bundleConfig;
        parent::__construct('test', true);
    }

    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new DataMapperBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            // Minimal framework configuration
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => true,
                'http_method_override' => false,
                'handle_all_throwables' => true,
                'php_errors' => [
                    'log' => true,
                ],
            ]);

            // Load DataMapper bundle configuration
            if (!empty($this->bundleConfig)) {
                $container->loadFromExtension('kassko_data_mapper', $this->bundleConfig);
            }

            // Make services public for testing
            $container->addCompilerPass(new class implements CompilerPassInterface {
                public function process(ContainerBuilder $container): void
                {
                    $servicesToMakePublic = [
                        'kassko_data_mapper.data_mapper',
                        'kassko_data_mapper.service_resolver',
                        'kassko_data_mapper.data_collector',
                    ];

                    foreach ($servicesToMakePublic as $serviceId) {
                        if ($container->hasDefinition($serviceId)) {
                            $container->getDefinition($serviceId)->setPublic(true);
                        }
                    }
                }
            });
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/kassko_data_mapper_bundle/cache/' . spl_object_hash($this);
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/kassko_data_mapper_bundle/log';
    }
}
