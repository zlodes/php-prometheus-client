<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Collector;

use Mockery;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Log\NullLogger;
use Zlodes\PrometheusExporter\Collector\CollectorFactory;
use Zlodes\PrometheusExporter\Exceptions\MetricNotFound;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\Registry\Registry;
use Zlodes\PrometheusExporter\Storage\Storage;

class CollectorFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCounterFound(): void
    {
        $factory = new CollectorFactory(
            $registryMock = Mockery::mock(Registry::class),
            Mockery::mock(Storage::class),
            new NullLogger(),
        );

        $registryMock
            ->expects('getCounter')
            ->with('counter_name')
            ->andReturn(new Counter('counter_name', 'Foo'));

        $factory->counter('counter_name');
    }

    public function testCounterNotFound(): void
    {
        $factory = new CollectorFactory(
            $registryMock = Mockery::mock(Registry::class),
            Mockery::mock(Storage::class),
            new NullLogger(),
        );

        $registryMock
            ->expects('getCounter')
            ->with('counter_name')
            ->andThrow(new MetricNotFound());

        $this->expectException(MetricNotFound::class);

        $factory->counter('counter_name');
    }

    public function testGaugeFound(): void
    {
        $factory = new CollectorFactory(
            $registryMock = Mockery::mock(Registry::class),
            Mockery::mock(Storage::class),
            new NullLogger(),
        );

        $registryMock
            ->expects('getGauge')
            ->with('gauge_name')
            ->andReturn(new Gauge('gauge_name', 'Foo'));

        $factory->gauge('gauge_name');
    }

    public function testGaugeNotFound(): void
    {
        $factory = new CollectorFactory(
            $registryMock = Mockery::mock(Registry::class),
            Mockery::mock(Storage::class),
            new NullLogger(),
        );

        $registryMock
            ->expects('getGauge')
            ->with('gauge_name')
            ->andThrow(new MetricNotFound());

        $this->expectException(MetricNotFound::class);

        $factory->gauge('gauge_name');
    }
}
