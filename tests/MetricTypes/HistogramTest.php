<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\MetricTypes;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Zlodes\PrometheusClient\Metric\Histogram;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Metric\MetricType;

class HistogramTest extends TestCase
{
    public function testsCorrectType(): void
    {
        $histogram = new Histogram('response_time', 'App response time');

        self::assertSame(MetricType::HISTOGRAM, $histogram->getType());
    }

    public function testGetDependentMetrics(): void
    {
        $histogram = new Histogram('response_time', 'App response time');

        $expectedDependentMetrics = [
            'response_time_sum',
            'response_time_count',
        ];

        self::assertEquals($expectedDependentMetrics, $histogram->getDependentMetrics());
    }

    public function testWithBuckets(): void
    {
        $histogram = new Histogram('response_time', 'App response time');

        $newHistogram = $histogram->withBuckets([0, 1, 2]);

        self::assertNotSame($histogram, $newHistogram);
        self::assertEquals([0, 1, 2], $newHistogram->getBuckets());
    }

    #[DataProvider('invalidBuckets')]
    public function testBucketsValidation(array $buckets): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Histogram('response_time', 'App response time'))
            ->withBuckets($buckets);
    }

    public static function invalidBuckets(): iterable
    {
        yield 'empty' => [[]];
        yield 'not sorted' => [[1, 0]];
        yield 'not unique' => [[1, 1]];
        yield 'not positive' => [[-1]];
        yield 'not numeric' => [['a']];
    }
}
