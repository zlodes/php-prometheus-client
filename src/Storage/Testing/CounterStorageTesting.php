<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Testing;

use Zlodes\PrometheusClient\Storage\Commands\IncrementCounter;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

// phpcs:ignore
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;

/**
 * @codeCoverageIgnore
 */
trait CounterStorageTesting
{
    public function testFullCycle(): void
    {
        $storage = $this->createStorage();

        assertEmpty([...$storage->fetchCounters()]);

        // Increment Bob by 1
        $storage->incrementCounter(
            new IncrementCounter(
                new MetricNameWithLabels('mileage', ['name' => 'Bob'])
            ),
        );

        // Increment Bob by 9.5
        $storage->incrementCounter(
            new IncrementCounter(
                new MetricNameWithLabels('mileage', ['name' => 'Bob']),
                8.5
            ),
        );

        // Increment Bob by 0.5
        $storage->incrementCounter(
            new IncrementCounter(
                new MetricNameWithLabels('mileage', ['name' => 'Bob']),
                0.5
            ),
        );

        // Increment Alice by 3
        $storage->incrementCounter(
            new IncrementCounter(
                new MetricNameWithLabels('mileage', ['name' => 'Alice']),
                3
            ),
        );

        /** @var list<MetricValue> $counters */
        $counters = [...$storage->fetchCounters()];
        assertCount(2, $counters);

        // Bob
        assertSame('mileage', $counters[0]->metricNameWithLabels->metricName);
        assertSame(['name' => 'Bob'], $counters[0]->metricNameWithLabels->labels);
        assertEquals(10, $counters[0]->value);

        // Alice
        assertSame('mileage', $counters[1]->metricNameWithLabels->metricName);
        assertSame(['name' => 'Alice'], $counters[1]->metricNameWithLabels->labels);
        assertEquals(3, $counters[1]->value);

        // Clean
        $storage->clearCounters();
        assertEmpty([...$storage->fetchCounters()]);
    }

    abstract protected function createStorage(): CounterStorage;
}
