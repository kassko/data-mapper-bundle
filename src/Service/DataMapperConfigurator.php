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

use Kassko\Bundle\DataMapperBundle\DataCollector\DataMapperDataCollector;
use Kassko\DataMapper\DataMapper;

/**
 * Configurator for the DataMapper service.
 *
 * This configurator handles post-construction setup of the DataMapper,
 * such as enabling data lineage collection, cascade collection, and connecting to the profiler.
 */
class DataMapperConfigurator
{
    public function __construct(
        private bool $enableLineageCollection,
        private bool $enableCascadeCollection = false,
        private ?DataMapperDataCollector $dataCollector = null
    ) {
    }

    /**
     * Configure the DataMapper instance.
     */
    public function configure(DataMapper $dataMapper): void
    {
        // Enable lineage collection if configured
        if ($this->enableLineageCollection) {
            $dataMapper->enableLineageCollection();
        }

        // Enable cascade collection if configured and method exists
        // (this feature may be added in a future version of data-mapper)
        if ($this->enableCascadeCollection && method_exists($dataMapper, 'enableCascadeCollection')) {
            $dataMapper->enableCascadeCollection();
        }

        // Connect the data collector if available
        if ($this->dataCollector !== null) {
            $this->dataCollector->setDataMapper($dataMapper);
        }
    }
}
