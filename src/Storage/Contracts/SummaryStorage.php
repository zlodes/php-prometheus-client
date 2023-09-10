<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Contracts;

use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Storage\Commands\UpdateSummary;
use Zlodes\PrometheusClient\Storage\DTO\SummaryMetricValue;

interface SummaryStorage
{
    /**
     * @throws StorageWriteException
     */
    public function updateSummary(UpdateSummary $command): void;

    /**
     * @return iterable<int, SummaryMetricValue>
     */
    public function fetchSummaries(): iterable;

    /**
     * @throws StorageWriteException
     */
    public function clearSummaries(): void;
}
