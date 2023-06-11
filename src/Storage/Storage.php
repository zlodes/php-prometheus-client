<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage;

use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

interface Storage
{
    /**
     * @return iterable<int, MetricValue>
     *
     * @throws StorageReadException
     */
    public function fetch(): iterable;

    /**
     * Removes all the keys
     *
     * @throws StorageWriteException
     */
    public function clear(): void;

    /**
     * @throws StorageWriteException
     */
    public function setValue(MetricValue $value): void;

    /**
     * @throws StorageWriteException
     */
    public function incrementValue(MetricValue $value): void;

    /**
     * @param MetricValue $value
     * @param non-empty-list<float> $buckets
     *
     * @throws StorageWriteException
     */
    public function persistHistogram(MetricValue $value, array $buckets): void;
}
