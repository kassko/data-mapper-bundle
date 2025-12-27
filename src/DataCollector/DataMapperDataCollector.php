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

namespace Kassko\Bundle\DataMapperBundle\DataCollector;

use Kassko\DataMapper\DataCollector\DataLineageCollector;
use Kassko\DataMapper\DataCollector\LineageEvent;
use Kassko\DataMapper\DataMapper;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Symfony DataCollector for DataMapper data lineage.
 *
 * This collector integrates with the Symfony Web Profiler to display
 * data lineage information collected during the request lifecycle.
 */
class DataMapperDataCollector extends AbstractDataCollector
{
    private ?DataMapper $dataMapper = null;
    private bool $enabled;

    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * Set the DataMapper instance to collect data from.
     */
    public function setDataMapper(DataMapper $dataMapper): void
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        if (!$this->enabled || $this->dataMapper === null) {
            $this->data = [
                'enabled' => false,
                'events' => [],
                'summary' => [],
            ];
            return;
        }

        $lineageCollector = $this->dataMapper->getLineageCollector();

        $this->data = [
            'enabled' => $lineageCollector->isEnabled(),
            'events' => $lineageCollector->getEventsAsArrays(),
            'summary' => $lineageCollector->getSummary(),
            'events_by_type' => $this->groupEventsByType($lineageCollector),
            'events_by_class' => $this->groupEventsByClass($lineageCollector),
        ];
    }

    /**
     * Group events by their type for easier visualization.
     */
    private function groupEventsByType(DataLineageCollector $collector): array
    {
        $grouped = [];
        $types = [
            LineageEvent::TYPE_DATASOURCE_CALL,
            LineageEvent::TYPE_PROPERTY_HYDRATION,
            LineageEvent::TYPE_PROPERTY_SKIPPED,
            LineageEvent::TYPE_PROPERTY_TRANSFORMED,
            LineageEvent::TYPE_HOOK_EXECUTED,
            LineageEvent::TYPE_CUSTOM_HYDRATOR,
            LineageEvent::TYPE_CONTEXT_SET,
            LineageEvent::TYPE_DECISION_POINT,
        ];

        foreach ($types as $type) {
            $events = $collector->getEventsByType($type);
            if (!empty($events)) {
                $grouped[$type] = array_map(fn($e) => $e->toArray(), $events);
            }
        }

        return $grouped;
    }

    /**
     * Group events by object class for tree visualization.
     */
    private function groupEventsByClass(DataLineageCollector $collector): array
    {
        $grouped = [];

        foreach ($collector->getEvents() as $event) {
            $class = $event->objectClass;
            if (!isset($grouped[$class])) {
                $grouped[$class] = [];
            }
            $grouped[$class][] = $event->toArray();
        }

        return $grouped;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->data = [];

        if ($this->dataMapper !== null) {
            $this->dataMapper->getLineageCollector()->reset();
        }
    }

    /**
     * Check if data lineage collection is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->data['enabled'] ?? false;
    }

    /**
     * Get all collected events.
     */
    public function getEvents(): array
    {
        return $this->data['events'] ?? [];
    }

    /**
     * Get the summary of collected data.
     */
    public function getSummary(): array
    {
        return $this->data['summary'] ?? [];
    }

    /**
     * Get events grouped by type.
     */
    public function getEventsByType(): array
    {
        return $this->data['events_by_type'] ?? [];
    }

    /**
     * Get events grouped by class.
     */
    public function getEventsByClass(): array
    {
        return $this->data['events_by_class'] ?? [];
    }

    /**
     * Get the total number of events.
     */
    public function getEventCount(): int
    {
        return $this->data['summary']['totalEvents'] ?? 0;
    }

    /**
     * Get the count of DataSource calls.
     */
    public function getDataSourceCallCount(): int
    {
        return $this->data['summary']['eventsByType'][LineageEvent::TYPE_DATASOURCE_CALL] ?? 0;
    }

    /**
     * Get the count of property hydrations.
     */
    public function getHydrationCount(): int
    {
        return $this->data['summary']['eventsByType'][LineageEvent::TYPE_PROPERTY_HYDRATION] ?? 0;
    }

    /**
     * Get the count of skipped properties.
     */
    public function getSkippedCount(): int
    {
        return $this->data['summary']['eventsByType'][LineageEvent::TYPE_PROPERTY_SKIPPED] ?? 0;
    }

    /**
     * Get the maximum hydration depth.
     */
    public function getMaxDepth(): int
    {
        return $this->data['summary']['maxDepth'] ?? 0;
    }

    /**
     * Get the total duration of data operations.
     */
    public function getTotalDuration(): float
    {
        return $this->data['summary']['totalDuration'] ?? 0.0;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTemplate(): ?string
    {
        return '@DataMapper/Collector/data_mapper.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'kassko_data_mapper';
    }
}
