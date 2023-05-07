<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Collector;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Throwable;
use Zlodes\PrometheusExporter\Collector\PersistentCollector;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\Normalization\JsonMetricKeyDenormalizer;
use Zlodes\PrometheusExporter\Registry\ArrayRegistry;
use Zlodes\PrometheusExporter\Registry\Registry;
use Zlodes\PrometheusExporter\Storage\InMemoryStorage;
use Zlodes\PrometheusExporter\Storage\Storage;

final class PersistentCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @throws Throwable
     */
    public function testWriteAndRequest(): void
    {
        $collector = new PersistentCollector(
            $registry = new ArrayRegistry(),
            $storage = new InMemoryStorage(),
            new JsonMetricKeyDenormalizer(),
            new NullLogger(),
        );

        $registry->registerMetric(
            new Counter('foo', 'bar', )
        );

        $registry->registerMetric(
            new Gauge('bar', 'baz')
        );

        $collector->counterIncrement('foo', ['bar' => 'baz']);
        $collector->counterIncrement('foo', ['bar' => 'baz'], 1);
        $collector->counterIncrement('foo', ['baz' => 'qux'], 4);

        $collector->gaugeSet('bar', value: 100);
        $collector->gaugeIncrement('bar');

        $values = $storage->fetch();

        self::assertEquals(
            [
                'foo|{"bar":"baz"}' => 2.0,
                'foo|{"baz":"qux"}' => 4.0,
                'bar' => 101.0,
            ],
            $values
        );
    }

    public function testCounterNotFound(): void
    {
        $collector = new PersistentCollector(
            $registryMock = Mockery::mock(Registry::class),
            Mockery::mock(Storage::class),
            new JsonMetricKeyDenormalizer(),
            $loggerMock = Mockery::mock(LoggerInterface::class),
        );

        $registryMock
            ->expects('getCounter')
            ->with('foo')
            ->andReturnNull();

        $loggerMock->expects('alert');

        $collector->counterIncrement('foo');
    }

    public function testGaugeNotFound(): void
    {
        $collector = new PersistentCollector(
            $registryMock = Mockery::mock(Registry::class),
            Mockery::mock(Storage::class),
            new JsonMetricKeyDenormalizer(),
            $loggerMock = Mockery::mock(LoggerInterface::class),
        );

        $registryMock
            ->expects('getGauge')
            ->twice()
            ->with('bar')
            ->andReturnNull();

        $loggerMock
            ->expects('alert')
            ->twice();

        $collector->gaugeSet('bar');
        $collector->gaugeIncrement('bar');
    }

    public function testRepositoryErrorWhileIncrementingCounter(): void
    {
        $collector = new PersistentCollector(
            $registryMock = Mockery::mock(Registry::class),
            $storageMock = Mockery::mock(Storage::class),
            new JsonMetricKeyDenormalizer(),
            $loggerMock = Mockery::mock(LoggerInterface::class),
        );

        $registryMock
            ->expects('getCounter')
            ->with('foo')
            ->andReturn(new Counter('foo', 'help'));

        $storageMock
            ->expects('incrementValue')
            ->with('foo|{"bar":"baz"}', 2.0)
            ->andThrow(new RuntimeException('Something went wrong'));

        $loggerMock
            ->expects('error');

        $collector->counterIncrement('foo', ['bar' => 'baz'], 2.0);
    }

    public function testRepositoryErrorWhileIncrementingGauge(): void
    {
        $collector = new PersistentCollector(
            $registryMock = Mockery::mock(Registry::class),
            $storageMock = Mockery::mock(Storage::class),
            new JsonMetricKeyDenormalizer(),
            $loggerMock = Mockery::mock(LoggerInterface::class),
        );

        $registryMock
            ->expects('getGauge')
            ->with('bar')
            ->andReturn(new Gauge('bar', 'help'));

        $storageMock
            ->expects('incrementValue')
            ->with('bar', 3.0)
            ->andThrow(new RuntimeException('Something went wrong'));

        $loggerMock
            ->expects('error');

        $collector->gaugeIncrement('bar', value: 3.0);
    }

    public function testRepositoryErrorWhileSettingGaugeValue(): void
    {
        $collector = new PersistentCollector(
            $registryMock = Mockery::mock(Registry::class),
            $storageMock = Mockery::mock(Storage::class),
            new JsonMetricKeyDenormalizer(),
            $loggerMock = Mockery::mock(LoggerInterface::class),
        );

        $registryMock
            ->expects('getGauge')
            ->with('bar')
            ->andReturn(new Gauge('bar', 'help'));

        $storageMock
            ->expects('setValue')
            ->with('bar', 42.0)
            ->andThrow(new RuntimeException('Something went wrong'));

        $loggerMock
            ->expects('error');

        $collector->gaugeSet('bar', value: 42.0);
    }
}
