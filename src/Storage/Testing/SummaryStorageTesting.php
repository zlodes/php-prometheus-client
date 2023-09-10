<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Testing;

use Zlodes\PrometheusClient\Storage\Commands\UpdateSummary;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\SummaryMetricValue;

// phpcs:ignore
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;

/**
 * @codeCoverageIgnore
 */
trait SummaryStorageTesting
{
    public function testFullCycle(): void
    {
        $storage = $this->createStorage();

        assertEmpty([...$storage->fetchSummaries()]);

        $storage->updateSummary(
            new UpdateSummary(
                new MetricNameWithLabels('memory_usage', ['foo' => 'bar']),
                300,
            )
        );

        $storage->updateSummary(
            new UpdateSummary(
                new MetricNameWithLabels('memory_usage', ['bar' => 'baz']),
                100,
            )
        );

        $storage->updateSummary(
            new UpdateSummary(
                new MetricNameWithLabels('memory_usage', ['foo' => 'bar']),
                500,
            )
        );

        $storage->updateSummary(
            new UpdateSummary(
                new MetricNameWithLabels('cpu_usage'),
                0.345,
            )
        );

        $storage->updateSummary(
            new UpdateSummary(
                new MetricNameWithLabels('cpu_usage'),
                1.25,
            )
        );

        /** @var list<SummaryMetricValue> $fetched */
        $fetched = [...$storage->fetchSummaries()];
        assertCount(3, $fetched);

        // memory_usage foo
        assertSame('memory_usage', $fetched[0]->metricNameWithLabels->metricName);
        assertSame(['foo' => 'bar'], $fetched[0]->metricNameWithLabels->labels);
        assertEquals([300, 500], $fetched[0]->elements);

        // memory_usage bar
        assertSame('memory_usage', $fetched[1]->metricNameWithLabels->metricName);
        assertSame(['bar' => 'baz'], $fetched[1]->metricNameWithLabels->labels);
        assertEquals([100], $fetched[1]->elements);

        // cpu_usage
        assertSame('cpu_usage', $fetched[2]->metricNameWithLabels->metricName);
        assertSame([], $fetched[2]->metricNameWithLabels->labels);
        assertEquals([0.345, 1.25], $fetched[2]->elements);

        $storage->clearSummaries();
        assertEmpty([...$storage->fetchSummaries()]);
    }

    abstract protected function createStorage(): SummaryStorage;
}
