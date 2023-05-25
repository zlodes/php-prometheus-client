<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Collector\ByType;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Zlodes\PrometheusExporter\Collector\ByType\HistogramCollector;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusExporter\Exceptions\StorageWriteException;
use Zlodes\PrometheusExporter\MetricTypes\Histogram;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;
use Zlodes\PrometheusExporter\Storage\Storage;

class HistogramCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testUpdate(): void
    {
        $histogram = new Histogram('response_time', 'App response time');

        $collector = new HistogramCollector(
            $histogram,
            $storageMock = Mockery::mock(Storage::class),
            new NullLogger()
        );

        /** @var MetricValue $metricValue */
        $storageMock
            ->expects('persistHistogram')
            ->with(
                Mockery::capture($metricValue),
                $histogram->getBuckets()
            );

        $collector
            ->withLabels(['route' => '/'])
            ->update(0.65);

        self::assertEquals('response_time', $metricValue->metricNameWithLabels->metricName);
        self::assertEquals(0.65, $metricValue->value);
        self::assertEquals(['route' => '/'], $metricValue->metricNameWithLabels->labels);
    }

    public function testTimer(): void
    {
        $histogram = new Histogram('response_time', 'App response time');

        $collector = new HistogramCollector(
            $histogram,
            $storageMock = Mockery::mock(Storage::class),
            new NullLogger()
        );

        /** @var MetricValue $metricValue */
        $storageMock
            ->expects('persistHistogram')
            ->with(
                Mockery::capture($metricValue),
                $histogram->getBuckets()
            );

        $timer = $collector
            ->withLabels(['route' => '/'])
            ->startTimer();

        usleep(20000);

        $timer->stop();

        self::assertEquals('response_time', $metricValue->metricNameWithLabels->metricName);
        self::assertTrue($metricValue->value > 0.02 && $metricValue->value < 0.021);
        self::assertEquals(['route' => '/'], $metricValue->metricNameWithLabels->labels);
    }

    public function testsPersistError(): void
    {
        $histogram = new Histogram('response_time', 'App response time');

        $collector = new HistogramCollector(
            $histogram,
            $storageMock = Mockery::mock(Storage::class),
            $loggerMock = Mockery::mock(LoggerInterface::class)
        );

        $storageMock
            ->expects('persistHistogram')
            ->andThrow(new StorageWriteException('Cannot write'));

        $loggerMock
            ->expects('error');

        $collector->update(42);
    }
}
