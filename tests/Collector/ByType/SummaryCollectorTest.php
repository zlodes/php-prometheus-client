<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Collector\ByType;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Zlodes\PrometheusClient\Collector\ByType\HistogramCollector;
use Zlodes\PrometheusClient\Collector\ByType\SummaryCollector;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Metric\Summary;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\Storage;

class SummaryCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testUpdate(): void
    {
        $summary = new Summary('response_time', 'App response time');

        $collector = new SummaryCollector(
            $summary,
            $storageMock = Mockery::mock(Storage::class),
            new NullLogger()
        );

        /** @var MetricValue $metricValue */
        $storageMock
            ->expects('persistSummary')
            ->with(
                Mockery::capture($metricValue)
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
        $summary = new Summary('response_time', 'App response time');

        $collector = new SummaryCollector(
            $summary,
            $storageMock = Mockery::mock(Storage::class),
            new NullLogger()
        );

        /** @var MetricValue $metricValue */
        $storageMock
            ->expects('persistSummary')
            ->with(
                Mockery::capture($metricValue)
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
        $summary = new Summary('response_time', 'App response time');

        $collector = new SummaryCollector(
            $summary,
            $storageMock = Mockery::mock(Storage::class),
            $loggerMock = Mockery::mock(LoggerInterface::class)
        );

        /** @var MetricValue $metricValue */
        $storageMock
            ->expects('persistSummary')
            ->andThrow(new StorageWriteException('Cannot write'));

        $loggerMock
            ->expects('error');

        $collector->update(42);
    }
}
