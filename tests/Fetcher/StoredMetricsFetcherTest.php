<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Fetcher;

use Generator;
use Mockery;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Fetcher\DTO\FetchedMetric;
use Zlodes\PrometheusClient\Fetcher\StoredMetricsFetcher;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Metric\Summary;
use Zlodes\PrometheusClient\Registry\ArrayRegistry;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;
use Zlodes\PrometheusClient\Storage\DTO\HistogramMetricValue;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\DTO\SummaryMetricValue;

/**
 * Kinda stupid test to check that the fetcher return metric as expected: with correct values and order
 */
class StoredMetricsFetcherTest extends TestCase
{
    public function testFetch(): void
    {
        // Arrange

        $registry = new ArrayRegistry();
        $counterStorageMock = Mockery::mock(CounterStorage::class);
        $gaugeStorageMock = Mockery::mock(GaugeStorage::class);
        $histogramStorageMock = Mockery::mock(HistogramStorage::class);
        $summaryStorageMock = Mockery::mock(SummaryStorage::class);

        $stepsCounter = new Counter('steps', 'Number of steps');
        $temperatureGauge = new Gauge('temperature', 'Person temperature');
        $reqDurationHistogram = (new Histogram('http_request_duration_seconds', 'Request duration'))
            ->withBuckets([5, 10]);
        $memoryUsageSummary = (new Summary('memory_usage', 'Memory usage'))
            ->withQuantiles([0.5, 0.99]);

        $registry->registerMetric($stepsCounter);
        $registry->registerMetric($temperatureGauge);
        $registry->registerMetric($reqDurationHistogram);
        $registry->registerMetric($memoryUsageSummary);

        $counterStorageMock
            ->expects('fetchCounters')
            ->andReturnUsing(static function (): Generator {
                yield new MetricValue(
                    new MetricNameWithLabels('steps', ['person' => 'Alice']),
                    42
                );

                yield new MetricValue(
                    new MetricNameWithLabels('steps', ['person' => 'Bob']),
                    69
                );

                yield new MetricValue(
                    new MetricNameWithLabels('steps', ['person' => 'John']),
                    100500
                );
            });

        $gaugeStorageMock
            ->expects('fetchGauges')
            ->andReturnUsing(static function (): Generator {
                yield new MetricValue(
                    new MetricNameWithLabels('temperature', ['person' => 'Alice']),
                    36.6
                );

                yield new MetricValue(
                    new MetricNameWithLabels('temperature', ['person' => 'Bob']),
                    36.8
                );

                yield new MetricValue(
                    new MetricNameWithLabels('temperature', ['person' => 'John']),
                    37.1
                );
            });

        $histogramStorageMock
            ->expects('fetchHistograms')
            ->andReturnUsing(static function (): Generator {
                yield new HistogramMetricValue(
                    new MetricNameWithLabels('http_request_duration_seconds', ['api_version' => '1']),
                    ["5" => 3, "10" => 5, "+Inf" => 6],
                    49,
                    6
                );

                yield new HistogramMetricValue(
                    new MetricNameWithLabels('http_request_duration_seconds', ['api_version' => '2']),
                    ["5" => 6, "10" => 8, "+Inf" => 8],
                    32,
                    8
                );
            });

        $summaryStorageMock
            ->expects('fetchSummaries')
            ->andReturnUsing(static function(): Generator {
                yield new SummaryMetricValue(
                    new MetricNameWithLabels('memory_usage'),
                    [300, 500, 200, 500, 400]
                );
            });

        // Act

        $fetcher = new StoredMetricsFetcher(
            $registry,
            $counterStorageMock,
            $gaugeStorageMock,
            $histogramStorageMock,
            $summaryStorageMock,
        );

        /** @var list<FetchedMetric> $metrics */
        $metrics = [...$fetcher->fetch()];

        // Assert

        self::assertCount(4, $metrics);

        // Counter
        $fetchedCounter = $metrics[0];
        self::assertSame($stepsCounter, $fetchedCounter->metric);
        self::assertCount(3, $fetchedCounter->values);

        // Counter: Alice
        self::assertSame($stepsCounter->name, $fetchedCounter->values[0]->metricNameWithLabels->metricName);
        self::assertSame(['person' => 'Alice'], $fetchedCounter->values[0]->metricNameWithLabels->labels);
        self::assertSame(42, $fetchedCounter->values[0]->value);

        // Counter: Bob
        self::assertSame($stepsCounter->name, $fetchedCounter->values[1]->metricNameWithLabels->metricName);
        self::assertSame(['person' => 'Bob'], $fetchedCounter->values[1]->metricNameWithLabels->labels);
        self::assertSame(69, $fetchedCounter->values[1]->value);

        // Counter: John
        self::assertSame($stepsCounter->name, $fetchedCounter->values[2]->metricNameWithLabels->metricName);
        self::assertSame(['person' => 'John'], $fetchedCounter->values[2]->metricNameWithLabels->labels);
        self::assertSame(100500, $fetchedCounter->values[2]->value);


        // Gauge
        $fetchedGauge = $metrics[1];
        self::assertSame($temperatureGauge, $fetchedGauge->metric);
        self::assertCount(3, $fetchedGauge->values);

        // Gauge: Alice
        self::assertSame($temperatureGauge->name, $fetchedGauge->values[0]->metricNameWithLabels->metricName);
        self::assertSame(['person' => 'Alice'], $fetchedGauge->values[0]->metricNameWithLabels->labels);
        self::assertSame(36.6, $fetchedGauge->values[0]->value);

        // Gauge: Bob
        self::assertSame($temperatureGauge->name, $fetchedGauge->values[1]->metricNameWithLabels->metricName);
        self::assertSame(['person' => 'Bob'], $fetchedGauge->values[1]->metricNameWithLabels->labels);
        self::assertSame(36.8, $fetchedGauge->values[1]->value);

        // Gauge: John
        self::assertSame($temperatureGauge->name, $fetchedGauge->values[2]->metricNameWithLabels->metricName);
        self::assertSame(['person' => 'John'], $fetchedGauge->values[2]->metricNameWithLabels->labels);
        self::assertSame(37.1, $fetchedGauge->values[2]->value);


        // Histogram
        $fetchedHistogram = $metrics[2];
        self::assertSame($reqDurationHistogram, $fetchedHistogram->metric);
        // (2 buckets + +Inf + sum + count) * 2 = 10
        self::assertCount(10, $fetchedHistogram->values);

        // Histogram: API v1
        self::assertSame($reqDurationHistogram->name, $fetchedHistogram->values[0]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '1', 'le' => '5'], $fetchedHistogram->values[0]->metricNameWithLabels->labels);
        self::assertSame(3, $fetchedHistogram->values[0]->value);

        self::assertSame($reqDurationHistogram->name, $fetchedHistogram->values[1]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '1', 'le' => '10'], $fetchedHistogram->values[1]->metricNameWithLabels->labels);
        self::assertSame(5, $fetchedHistogram->values[1]->value);

        self::assertSame($reqDurationHistogram->name, $fetchedHistogram->values[2]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '1', 'le' => '+Inf'], $fetchedHistogram->values[2]->metricNameWithLabels->labels);
        self::assertSame(6, $fetchedHistogram->values[2]->value);

        self::assertSame($reqDurationHistogram->name . "_sum", $fetchedHistogram->values[3]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '1'], $fetchedHistogram->values[3]->metricNameWithLabels->labels);
        self::assertSame(49.0, $fetchedHistogram->values[3]->value);

        self::assertSame($reqDurationHistogram->name . "_count", $fetchedHistogram->values[4]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '1'], $fetchedHistogram->values[4]->metricNameWithLabels->labels);
        self::assertSame(6, $fetchedHistogram->values[4]->value);

        // Histogram: API v2
        self::assertSame($reqDurationHistogram->name, $fetchedHistogram->values[5]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '2', 'le' => '5'], $fetchedHistogram->values[5]->metricNameWithLabels->labels);
        self::assertSame(6, $fetchedHistogram->values[5]->value);

        self::assertSame($reqDurationHistogram->name, $fetchedHistogram->values[6]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '2', 'le' => '10'], $fetchedHistogram->values[6]->metricNameWithLabels->labels);
        self::assertSame(8, $fetchedHistogram->values[6]->value);

        self::assertSame($reqDurationHistogram->name, $fetchedHistogram->values[7]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '2', 'le' => '+Inf'], $fetchedHistogram->values[7]->metricNameWithLabels->labels);
        self::assertSame(8, $fetchedHistogram->values[7]->value);

        self::assertSame($reqDurationHistogram->name . "_sum", $fetchedHistogram->values[8]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '2'], $fetchedHistogram->values[8]->metricNameWithLabels->labels);
        self::assertSame(32.0, $fetchedHistogram->values[8]->value);

        self::assertSame($reqDurationHistogram->name . "_count", $fetchedHistogram->values[9]->metricNameWithLabels->metricName);
        self::assertSame(['api_version' => '2'], $fetchedHistogram->values[9]->metricNameWithLabels->labels);
        self::assertSame(8, $fetchedHistogram->values[9]->value);

        // Summary
        $fetchedSummary = $metrics[3];
        self::assertSame($memoryUsageSummary, $fetchedSummary->metric);

        // (2 quantiles + sum + count) = 4
        self::assertCount(4, $fetchedSummary->values);

        self::assertSame($memoryUsageSummary->name, $fetchedSummary->values[0]->metricNameWithLabels->metricName);
        self::assertSame(['quantile' => '0.5'], $fetchedSummary->values[0]->metricNameWithLabels->labels);
        self::assertEquals(400, $fetchedSummary->values[0]->value);

        self::assertSame($memoryUsageSummary->name, $fetchedSummary->values[1]->metricNameWithLabels->metricName);
        self::assertSame(['quantile' => '0.99'], $fetchedSummary->values[1]->metricNameWithLabels->labels);
        self::assertEquals(500, $fetchedSummary->values[1]->value);

        self::assertSame($memoryUsageSummary->name . "_sum", $fetchedSummary->values[2]->metricNameWithLabels->metricName);
        self::assertSame([], $fetchedSummary->values[2]->metricNameWithLabels->labels);
        self::assertSame(1900, $fetchedSummary->values[2]->value);

        self::assertSame($memoryUsageSummary->name . "_count", $fetchedSummary->values[3]->metricNameWithLabels->metricName);
        self::assertSame([], $fetchedSummary->values[3]->metricNameWithLabels->labels);
        self::assertSame(5, $fetchedSummary->values[3]->value);
    }
}
