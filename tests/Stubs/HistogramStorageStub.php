<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Stubs;

use Zlodes\PrometheusClient\Storage\Commands\UpdateHistogram;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;

final class HistogramStorageStub implements HistogramStorage
{
    public function updateHistogram(UpdateHistogram $command): void
    {
    }

    public function fetchHistograms(): iterable
    {
        return [];
    }

    public function clearHistograms(): void
    {
    }
}
