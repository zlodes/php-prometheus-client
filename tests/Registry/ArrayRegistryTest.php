<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusExporter\Exceptions\MetricAlreadyRegistered;
use Zlodes\PrometheusExporter\Exceptions\MetricHasWrongType;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\MetricTypes\Histogram;
use Zlodes\PrometheusExporter\Registry\ArrayRegistry;

final class ArrayRegistryTest extends TestCase
{
    public function testRegisterAndGet(): void
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
            $registry->getMetric('bar_gauge')
        );

        self::assertNull(
            $registry->getMetric('nonexistent')
        );

        self::assertNotNull(
            $registry->getCounter('foo_counter')
        );

        self::assertNotNull(
            $registry->getGauge('bar_gauge')
        );

        self::assertNotNull(
            $registry->getHistogram('baz_histogram')
        );
    }

    public function testDoubleRegistration(): void
    {
        $registry = new ArrayRegistry();

        $registry->registerMetric(
            new Counter('foo_counter', 'help')
        );

        $this->expectException(MetricAlreadyRegistered::class);

        $registry->registerMetric(
            new Counter('foo_counter', 'help')
        );
    }

    public function testGetCounterWithWrongType(): void
    {
        $registry = new ArrayRegistry();

        $registry->registerMetric(
            new Gauge('gauge', 'help')
        );

        $this->expectException(MetricHasWrongType::class);

        self::assertNull(
            $registry->getCounter('gauge')
        );
    }

    public function testGetGaugeWithWrongType(): void
    {
        $registry = new ArrayRegistry();

        $registry->registerMetric(
            new Counter('counter', 'help')
        );

        $this->expectException(MetricHasWrongType::class);

        self::assertNull(
            $registry->getGauge('counter')
        );
    }

    public function testGetHistogramWithWrongType(): void
    {
        $registry = new ArrayRegistry();

        $registry->registerMetric(
            new Counter('counter', 'help')
        );

        $this->expectException(MetricHasWrongType::class);

        self::assertNull(
            $registry->getHistogram('counter')
        );
    }
}
