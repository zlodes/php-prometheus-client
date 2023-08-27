<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Metric;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Metric\Metric;

class BaseMetricTest extends TestCase
{
    #[DataProvider('wrongNamesDataProvider')]
    public function testWrongNames(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Counter($name, 'help');
    }

    #[DataProvider('wrongLabelsDataProvider')]
    public function testWrongLabels(array $labels): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Counter('foo', 'help', $labels);
    }

    public static function wrongNamesDataProvider(): iterable
    {
        yield 'empty' => [""];
        yield 'with space' => ["foo bar"];
        yield 'only _' => ["_"];
        yield 'start from numeric' => ["099_foo"];
    }

    public static function wrongLabelsDataProvider(): iterable
    {
        yield 'numbers list' => [[1, 2, 3]];
        yield 'strings list' => [['foo', 'bar']];
        yield 'empty string' => [['']];
        yield 'one empty string' => [['foo' => 'bar', 'baz' => '']];
        yield 'non-string values' => [['foo' => 'bar', 'baz' => 42]];
    }

    #[DataProvider('prometheusMetricNameDataProvider')]
    public function testMetricsHaveCorrectPrometheusType(string $expectedName, Metric $metric): void
    {
        self::assertEquals(
            $expectedName,
            $metric->getPrometheusType()
        );
    }

    public static function prometheusMetricNameDataProvider(): iterable
    {
        yield 'counter' => ['counter', new Counter('foo', 'help')];
        yield 'gauge' => ['gauge', new Gauge('foo', 'help')];
        yield 'histogram' => ['histogram', new Histogram('foo', 'help')];
    }
}
