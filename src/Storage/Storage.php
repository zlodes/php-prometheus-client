<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

use Zlodes\PrometheusExporter\Exceptions\StorageReadException;
use Zlodes\PrometheusExporter\Exceptions\StorageWriteException;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;

interface Storage
{
    /**
     * @return list<MetricValue>
     *
     * @throws StorageReadException
     */
    public function fetch(): array;

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
