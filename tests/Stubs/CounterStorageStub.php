<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Stubs;

use Zlodes\PrometheusClient\Storage\Commands\IncrementCounter;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;

final class CounterStorageStub implements CounterStorage
{
    public function incrementCounter(IncrementCounter $command): void
    {
    }

    public function fetchCounters(): iterable
    {
        return [];
    }

    public function clearCounters(): void
    {
    }
}
