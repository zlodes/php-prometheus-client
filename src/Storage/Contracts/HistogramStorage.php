<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Contracts;

use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Storage\Commands\UpdateHistogram;
use Zlodes\PrometheusClient\Storage\DTO\HistogramMetricValue;

interface HistogramStorage
{
    /**
     * @throws StorageWriteException
     */
    public function updateHistogram(UpdateHistogram $command): void;

    /**
     * @return iterable<int, HistogramMetricValue>
     */
    public function fetchHistograms(): iterable;

    /**
     * @throws StorageWriteException
     */
    public function clearHistograms(): void;
}
