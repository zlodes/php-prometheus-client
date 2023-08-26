<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Collector;

use a;
use Mockery;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Log\NullLogger;
use Zlodes\PrometheusClient\Collector\CollectorFactory;
use Zlodes\PrometheusClient\Exception\MetricNotFoundException;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Registry\Registry;
use Zlodes\PrometheusClient\Storage\Storage;

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
            ->expects('getMetric')
            ->with('counter_name', Counter::class)
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
            ->expects('getMetric')
            ->with('counter_name', Counter::class)
            ->andThrow(new MetricNotFoundException());

        $this->expectException(MetricNotFoundException::class);

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
            ->expects('getMetric')
            ->with('gauge_name', Gauge::class)
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
            ->expects('getMetric')
            ->with('gauge_name', Gauge::class)
            ->andThrow(new MetricNotFoundException());

        $this->expectException(MetricNotFoundException::class);

        $factory->gauge('gauge_name');
    }

    public function testHistogramFound(): void
    {
        $factory = new CollectorFactory(
            $registryMock = Mockery::mock(Registry::class),
            Mockery::mock(Storage::class),
            new NullLogger(),
        );

        $registryMock
            ->expects('getMetric')
            ->with('histogram_name', Histogram::class)
            ->andReturn(new Histogram('histogram_name', 'Foo'));

        $factory->histogram('histogram_name');
    }

    public function testHistogramNotFound(): void
    {
        $factory = new CollectorFactory(
            $registryMock = Mockery::mock(Registry::class),
            Mockery::mock(Storage::class),
            new NullLogger(),
        );

        $registryMock
            ->expects('getMetric')
            ->with('histogram_name', Histogram::class)
            ->andThrow(new MetricNotFoundException());

        $this->expectException(MetricNotFoundException::class);

        $factory->histogram('histogram_name');
    }
}
