<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Registry;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Exception\MetricAlreadyRegisteredException;
use Zlodes\PrometheusClient\Exception\MetricHasWrongTypeException;
use Zlodes\PrometheusClient\Exception\MetricNotFoundException;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Metric\Metric;
use Zlodes\PrometheusClient\Registry\ArrayRegistry;

final class ArrayRegistryTest extends TestCase
{
    public function testRegisterAndGetSuccessful(): void
    {
        $registry = new ArrayRegistry();

        self::assertEmpty($registry->getAll());

        $registry->registerMetric(
            new Counter('foo_counter', 'help')
        );

        $registry->registerMetric(
            new Gauge('bar_gauge', 'help')
        );

        $registry->registerMetric(
            new Histogram('baz_histogram', 'help')
        );

        self::assertCount(3, $registry->getAll());

        self::assertInstanceOf(
            Gauge::class,
            $registry->getMetric('bar_gauge', Gauge::class)
        );

        self::assertInstanceOf(
            Counter::class,
            $registry->getMetric('foo_counter', Counter::class)
        );

        self::assertInstanceOf(
            Gauge::class,
            $registry->getMetric('bar_gauge', Gauge::class)
        );

        self::assertInstanceOf(
            Histogram::class,
            $registry->getMetric('baz_histogram', Histogram::class)
        );
    }

    public function testDoubleRegistration(): void
    {
        $registry = new ArrayRegistry();

        $registry->registerMetric(
            new Counter('foo_counter', 'help')
        );

        $this->expectException(MetricAlreadyRegisteredException::class);

        $registry->registerMetric(
            new Counter('foo_counter', 'help')
        );
    }

    public function testMetricNotFound(): void
    {
        $registry = new ArrayRegistry();

        $this->expectException(MetricNotFoundException::class);

        $registry->getMetric('foo', Counter::class);
    }

    #[DataProvider('wrongTypesDataProvider')]
    public function testWrongType(string $expectedClass, Metric $actualMetric): void
    {
        $registry = new ArrayRegistry();
        $registry->registerMetric($actualMetric);

        $this->expectException(MetricHasWrongTypeException::class);
        $this->expectExceptionMessage($actualMetric::class);
        $this->expectExceptionMessage($expectedClass);

        $registry->getMetric(
            $actualMetric->name,
            $expectedClass
        );
    }

    public static function wrongTypesDataProvider(): iterable
    {
        $counter = new Counter('counter', 'help');
        $gauge = new Gauge('gauge', 'help');
        $histogram = new Histogram('histogram', 'help');

        // Counter expected
        yield 'expected counter, gauge given' => [Counter::class, $gauge];
        yield 'expected counter, histogram given' => [Counter::class, $histogram];

        // Gauge expected
        yield 'expected gauge, counter given' => [Gauge::class, $counter];
        yield 'expected gauge, histogram given' => [Gauge::class, $histogram];

        // Histogram expected
        yield 'expected histogram, counter given' => [Histogram::class, $counter];
        yield 'expected histogram, gauge given' => [Histogram::class, $gauge];
    }
}
