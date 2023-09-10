<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Contracts;

use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Storage\Commands\IncrementCounter;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

interface CounterStorage
{
    /**
     * @throws StorageWriteException
     */
    public function incrementCounter(IncrementCounter $command): void;

    /**
     * @return iterable<int, MetricValue>
     *
     * @throws StorageReadException
     */
    public function fetchCounters(): iterable;

    /**
     * @throws StorageWriteException
     */
    public function clearCounters(): void;
}
