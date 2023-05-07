<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusExporter\Exceptions\MetricAlreadyRegistered;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\Registry\ArrayRegistry;

final class ArrayRegistryTest extends TestCase
{
    public function testRegisterAndGet(): void
    {
        $registry = new ArrayRegistry();

        self::assertEmpty(
            iterator_to_array($registry->getAll(), false)
        );

        $registry->registerMetric(
            new Counter('foo_counter', 'help')
        );

        $registry->registerMetric(
            new Gauge('bar_gauge', 'help')
        );

        self::assertCount(
            2,
            iterator_to_array($registry->getAll(), false)
        );

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

    public function testGetWrongType(): void
    {
        $registry = new ArrayRegistry();

        $registry->registerMetric(
            new Counter('counter', 'help')
        );

        $registry->registerMetric(
            new Gauge('gauge', 'help')
        );

        self::assertNull(
            $registry->getGauge('counter')
        );

        self::assertNull(
            $registry->getCounter('gauge')
        );
    }
}
