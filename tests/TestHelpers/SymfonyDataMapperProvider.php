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

namespace Kassko\Bundle\DataMapperBundle\Tests\TestHelpers;

use Kassko\Bundle\DataMapperBundle\Tests\Integration\TestKernel;
use Kassko\DataMapper\DataMapper;
use Kassko\DataMapper\Registry\LoaderRegistry;
use Kassko\DataMapper\Tests\TestHelpers\DataMapperProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * Symfony Bundle implementation of DataMapperProviderInterface.
 * 
 * This provider creates DataMapper instances using a real Symfony kernel,
 * allowing Core integration tests to run in a Symfony Bundle context.
 * 
 * The kernel is reused between tests when configuration is compatible,
 * which significantly improves test performance.
 */
class SymfonyDataMapperProvider implements DataMapperProviderInterface
{
    private static ?TestKernel $sharedKernel = null;
    private static array $sharedKernelConfig = [];
    private ?DataMapper $dataMapper = null;

    public function getDataMapper(array $services = [], array $config = []): DataMapper
    {
        // Build bundle configuration
        $bundleConfig = [];

        // Enable lineage collection if requested
        if (isset($config['enable_lineage_collection']) && $config['enable_lineage_collection']) {
            $bundleConfig['enable_lineage_collection'] = true;
        }

        // Check if we can reuse the shared kernel
        $canReuseKernel = self::$sharedKernel !== null 
            && empty($services) 
            && $bundleConfig === self::$sharedKernelConfig;

        if (!$canReuseKernel) {
            // Shutdown existing kernel if any
            if (self::$sharedKernel !== null) {
                self::$sharedKernel->shutdown();
                restore_exception_handler();
                self::$sharedKernel = null;
            }

            // Create and boot new kernel
            self::$sharedKernel = new TestKernel($bundleConfig);
            self::$sharedKernel->boot();
            self::$sharedKernelConfig = $bundleConfig;
            
            // Register custom services in the compiled container
            $container = self::$sharedKernel->getContainer();
            foreach ($services as $serviceId => $service) {
                $container->set($serviceId, $service);
            }
        }

        $this->dataMapper = self::$sharedKernel->getContainer()->get('kassko_data_mapper.data_mapper');
        
        // Ensure the loader is registered (may have been cleared by previous test tearDown)
        $this->dataMapper->ensureLoaderRegistered();

        return $this->dataMapper;
    }

    public function getContainer(): ?ContainerInterface
    {
        if (self::$sharedKernel === null) {
            return null;
        }

        return self::$sharedKernel->getContainer();
    }

    public function tearDown(): void
    {
        // Clear loader registry between tests but keep kernel alive
        LoaderRegistry::clear();
        
        // Clear DataMapper context for next test
        if ($this->dataMapper !== null) {
            $this->dataMapper->clearContext();
        }
        
        $this->dataMapper = null;
    }

    /**
     * Full cleanup - call this after all tests are done.
     */
    public static function shutdownSharedKernel(): void
    {
        if (self::$sharedKernel !== null) {
            self::$sharedKernel->shutdown();
            restore_exception_handler();
            self::$sharedKernel = null;
            self::$sharedKernelConfig = [];
        }

        // Clean up temp cache dirs
        $cacheDir = sys_get_temp_dir() . '/kassko_data_mapper_bundle';
        if (is_dir($cacheDir)) {
            @shell_exec("rm -rf " . escapeshellarg($cacheDir));
        }
    }

    public function getName(): string
    {
        return 'symfony';
    }
}
