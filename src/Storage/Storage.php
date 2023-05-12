<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

use Zlodes\PrometheusExporter\DTO\MetricValue;
use Zlodes\PrometheusExporter\Exceptions\StorageReadException;
use Zlodes\PrometheusExporter\Exceptions\StorageWriteException;

interface Storage
{
    /**
     * @return list<MetricValue> Metrics array with denormalized keys and values
     *
     * @throws StorageReadException
     */
    public function fetch(): array;

    /**
     * Removes all the keys
     *
     * @throws StorageWriteException
     */
    public function flush(): void;

    /**
     * @throws StorageWriteException
     */
    public function setValue(MetricValue $value): void;

    /**
     * @throws StorageWriteException
     */
    public function incrementValue(MetricValue $value): void;
}
