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

namespace Kassko\Bundle\DataMapperBundle\Tests\Integration\Portable;

use Kassko\Bundle\DataMapperBundle\Tests\TestHelpers\SymfonyDataMapperProvider;
use Kassko\DataMapper\Tests\Integration\Portable\DataMapperPortableTest as CoreDataMapperPortableTest;

/**
 * Runs Core DataMapper portable tests in Symfony Bundle context.
 * 
 * This test extends the Core library portable tests and injects
 * the SymfonyDataMapperProvider to run them with a real Symfony kernel.
 */
class DataMapperPortableTest extends CoreDataMapperPortableTest
{
    public static function setUpBeforeClass(): void
    {
        self::setDataMapperProvider(new SymfonyDataMapperProvider());
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        SymfonyDataMapperProvider::shutdownSharedKernel();
        parent::tearDownAfterClass();
    }
}
