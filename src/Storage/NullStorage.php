<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage;

use Zlodes\PrometheusClient\Storage\Commands\IncrementCounter;
use Zlodes\PrometheusClient\Storage\Commands\UpdateGauge;
use Zlodes\PrometheusClient\Storage\Commands\UpdateHistogram;
use Zlodes\PrometheusClient\Storage\Commands\UpdateSummary;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;

/** @codeCoverageIgnore */
final class NullStorage implements CounterStorage, GaugeStorage, HistogramStorage, SummaryStorage
{
    public function incrementCounter(IncrementCounter $command): void
    {
        // Do nothing
    }

    public function fetchCounters(): iterable
    {
        return [];
    }

    public function clearCounters(): void
    {
        // Do nothing
    }

    public function updateGauge(UpdateGauge $command): void
    {
        // Do nothing
    }

    public function fetchGauges(): iterable
    {
        return [];
    }

    public function clearGauges(): void
    {
        // Do nothing
    }

    public function updateHistogram(UpdateHistogram $command): void
    {
        // Do nothing
    }

    public function fetchHistograms(): iterable
    {
        return [];
    }

    public function clearHistograms(): void
    {
        // Do nothing
    }

    public function updateSummary(UpdateSummary $command): void
    {
        // Do nothing
    }

    public function fetchSummaries(): iterable
    {
        return [];
    }

    public function clearSummaries(): void
    {
        // Do nothing
    }
}
