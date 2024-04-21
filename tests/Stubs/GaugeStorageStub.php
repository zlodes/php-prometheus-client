<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Stubs;

use Zlodes\PrometheusClient\Storage\Commands\UpdateGauge;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;

final class GaugeStorageStub implements GaugeStorage
{
    public function updateGauge(UpdateGauge $command): void
    {
    }

    public function fetchGauges(): iterable
    {
        return [];
    }

    public function clearGauges(): void
    {
    }
}
