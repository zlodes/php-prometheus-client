<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Testing;

use PHPUnit\Framework\Attributes\DataProvider;
use Zlodes\PrometheusClient\Storage\Commands\UpdateHistogram;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;
use Zlodes\PrometheusClient\Storage\DTO\HistogramMetricValue;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

// phpcs:ignore
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertSame;

/**
 * @codeCoverageIgnore
 */
trait HistogramStorageTesting
{
    /**
     * @param non-empty-list<int|float> $buckets
     * @param non-empty-list<int|float> $values
     * @param array<string, int|float> $expectedBucketsValues
     */
    #[DataProvider('histogramDataProvider')]
    public function testFullCycle(
        array $buckets,
        array $values,
        array $expectedBucketsValues,
        int|float $expectedSum,
        int $expectedCount,
    ): void {
        $storage = $this->createStorage();

        assertEmpty([...$storage->fetchHistograms()]);

        $metricNameWithLabels = new MetricNameWithLabels('response_time');

        foreach ($values as $value) {
            $storage->updateHistogram(
                new UpdateHistogram(
                    $metricNameWithLabels,
                    $buckets,
                    $value
                )
            );
        }

        /** @var list<HistogramMetricValue> $fetched */
        $fetched = [...$storage->fetchHistograms()];
        assertCount(1, $fetched);

        $fetchedHistogram = $fetched[0];

        assertEquals($metricNameWithLabels, $fetchedHistogram->metricNameWithLabels);
        assertEquals($expectedBucketsValues, $fetchedHistogram->buckets);
        assertEquals($expectedSum, $fetchedHistogram->sum);
        assertSame($expectedCount, $fetchedHistogram->count);

        $storage->clearHistograms();
        assertEmpty([...$storage->fetchHistograms()]);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testHistogramWithLabels(): void
    {
        $storage = $this->createStorage();

        $storage->updateHistogram(
            new UpdateHistogram(
                new MetricNameWithLabels('some_metric', ['method' => 'GET']),
                [0, 1, 2, 3, 4, 5],
                1
            ),
        );

        $storage->updateHistogram(
            new UpdateHistogram(
                new MetricNameWithLabels('some_metric', ['method' => 'GET']),
                [0, 1, 2, 3, 4, 5],
                2
            ),
        );

        $storage->updateHistogram(
            new UpdateHistogram(
                new MetricNameWithLabels('some_metric', ['method' => 'GET']),
                [0, 1, 2, 3, 4, 5],
                5
            ),
        );

        $storage->updateHistogram(
            new UpdateHistogram(
                new MetricNameWithLabels('some_metric', ['method' => 'POST']),
                [0, 1, 2, 3, 4, 5],
                2
            ),
        );

        $storage->updateHistogram(
            new UpdateHistogram(
                new MetricNameWithLabels('some_metric', ['method' => 'POST']),
                [0, 1, 2, 3, 4, 5],
                3
            ),
        );

        $storage->updateHistogram(
            new UpdateHistogram(
                new MetricNameWithLabels('some_metric', ['method' => 'GET']),
                [0, 1, 2, 3, 4, 5],
                7
            ),
        );

        /** @var list<HistogramMetricValue> $fetchedHistograms */
        $fetchedHistograms = [...$storage->fetchHistograms()];

        assertCount(2, $fetchedHistograms);

        $expectedHistograms = [
            new HistogramMetricValue(
                new MetricNameWithLabels(
                    'some_metric',
                    ['method' => 'GET']
                ),
                [
                    '0' => 0,
                    '1' => 1,
                    '2' => 2,
                    '3' => 2,
                    '4' => 2,
                    '5' => 3,
                    '+Inf' => 4,
                ],
                15,
                4
            ),

            new HistogramMetricValue(
                new MetricNameWithLabels(
                    'some_metric',
                    ['method' => 'POST']
                ),
                [
                    '0' => 0,
                    '1' => 0,
                    '2' => 1,
                    '3' => 2,
                    '4' => 2,
                    '5' => 2,
                    '+Inf' => 2,
                ],
                5,
                2
            ),
        ];

        assertEquals($expectedHistograms, $fetchedHistograms);
    }

    public static function histogramDataProvider(): iterable
    {
        yield 'all zeroes' => [
            [0, 1, 2, 3, 4],
            [0, 0, 0, 0, 0],
            [
                "0" => 5,
                "1" => 5,
                "2" => 5,
                "3" => 5,
                "4" => 5,
                "+Inf" => 5,
            ],
            0,
            5,
        ];

        yield 'simple' => [
            [0, 1, 2, 3, 4, 5],
            [0, 1, 1, 2, 3, 3, 4, 5, 6],
            [
                "0" => 1,
                "1" => 3,
                "2" => 4,
                "3" => 6,
                "4" => 7,
                "5" => 8,
                "+Inf" => 9,
            ],
            25,
            9,
        ];

        yield 'complex' => [
            [0, 1, 2, 3, 3.5, 5, 10],
            [1, 1, 1, 2, 0, 5, 7.5, 10, 30, 0.7, 53, 3.5, 4, 4, 8],
            [
                "0" => 1,
                "1" => 5,
                "2" => 6,
                "3" => 6,
                "3.5" => 7,
                "5" => 10,
                "10" => 13,
                "+Inf" => 15,
            ],
            130.7,
            15,
        ];
    }

    abstract protected function createStorage(): HistogramStorage;
}
