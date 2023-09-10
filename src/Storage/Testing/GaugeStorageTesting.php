<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Testing;

use Zlodes\PrometheusClient\Storage\Commands\UpdateGauge;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;
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
trait GaugeStorageTesting
{
    public function testFullCycle(): void
    {
        $storage = $this->createStorage();

        assertEmpty([...$storage->fetchGauges()]);

        // Set Bob to 36.6
        $storage->updateGauge(
            new UpdateGauge(
                new MetricNameWithLabels('temperature', ['name' => 'Bob']),
                36.6
            ),
        );

        // Set Alice by 36.8
        $storage->updateGauge(
            new UpdateGauge(
                new MetricNameWithLabels('temperature', ['name' => 'Alice']),
                36.8
            ),
        );

        // Set Alice by 36.5
        $storage->updateGauge(
            new UpdateGauge(
                new MetricNameWithLabels('temperature', ['name' => 'Alice']),
                36.5
            ),
        );

        // Set Bob to 37.5
        $storage->updateGauge(
            new UpdateGauge(
                new MetricNameWithLabels('temperature', ['name' => 'Bob']),
                37.5
            ),
        );

        /** @var MetricValue $gauges */
        $gauges = [...$storage->fetchGauges()];
        assertCount(2, $gauges);

        // Bob
        assertSame('temperature', $gauges[0]->metricNameWithLabels->metricName);
        assertSame(['name' => 'Bob'], $gauges[0]->metricNameWithLabels->labels);
        assertEquals(37.5, $gauges[0]->value);

        // Alice
        assertSame('temperature', $gauges[1]->metricNameWithLabels->metricName);
        assertSame(['name' => 'Alice'], $gauges[1]->metricNameWithLabels->labels);
        assertEquals(36.5, $gauges[1]->value);

        // Clean
        $storage->clearGauges();
        assertEmpty([...$storage->fetchGauges()]);
    }

    abstract protected function createStorage(): GaugeStorage;
}
