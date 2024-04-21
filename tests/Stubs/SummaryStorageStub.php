<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Stubs;

use Zlodes\PrometheusClient\Storage\Commands\UpdateSummary;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;

final class SummaryStorageStub implements SummaryStorage
{
    public function updateSummary(UpdateSummary $command): void
    {
    }

    public function fetchSummaries(): iterable
    {
        return [];
    }

    public function clearSummaries(): void
    {
    }
}
