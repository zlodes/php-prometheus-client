<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Contracts;

use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Storage\Commands\UpdateGauge;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

interface GaugeStorage
{
    /**
     * @throws StorageWriteException
     */
    public function updateGauge(UpdateGauge $command): void;

    /**
     * @return iterable<int, MetricValue>
     *
     * @throws StorageReadException
     */
    public function fetchGauges(): iterable;

    /**
     * @throws StorageWriteException
     */
    public function clearGauges(): void;
}
