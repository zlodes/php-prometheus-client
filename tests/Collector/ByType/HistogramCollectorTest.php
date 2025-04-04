<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Collector\ByType;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Zlodes\PrometheusClient\Collector\ByType\HistogramCollector;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Storage\Commands\UpdateHistogram;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;

final class HistogramCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testUpdate(): void
    {
        $histogram = (new Histogram('response_time', 'App response time'))
            ->withBuckets([0.5, 0.6, 1]);

        $collector = new HistogramCollector(
            $histogram,
            $storageMock = Mockery::mock(HistogramStorage::class),
            new NullLogger()
        );

        /** @var UpdateHistogram $updateCommand */
        $storageMock
            ->expects('updateHistogram')
            ->with(
                Mockery::capture($updateCommand),
            );

        $collector
            ->withLabels(['route' => '/'])
            ->update(0.65);

        self::assertEquals('response_time', $updateCommand->metricNameWithLabels->metricName);
        self::assertEquals(0.65, $updateCommand->value);
        self::assertEquals(['route' => '/'], $updateCommand->metricNameWithLabels->labels);
        self::assertEquals([0.5, 0.6, 1], $updateCommand->buckets);
    }

    public function testTimer(): void
    {
        $histogram = new Histogram('response_time', 'App response time');

        $collector = new HistogramCollector(
            $histogram,
            $storageMock = Mockery::mock(HistogramStorage::class),
            new NullLogger()
        );

        /** @var UpdateHistogram $updateCommand */
        $storageMock
            ->expects('updateHistogram')
            ->with(
                Mockery::capture($updateCommand),
            );

        $timer = $collector
            ->withLabels(['route' => '/'])
            ->startTimer();

        usleep(20000);

        $timer->stop();

        self::assertEquals('response_time', $updateCommand->metricNameWithLabels->metricName);
        self::assertTrue($updateCommand->value > 0.02 && $updateCommand->value < 0.021);
        self::assertEquals(['route' => '/'], $updateCommand->metricNameWithLabels->labels);
    }

    public function testsPersistError(): void
    {
        $histogram = new Histogram('response_time', 'App response time');

        $collector = new HistogramCollector(
            $histogram,
            $storageMock = Mockery::mock(HistogramStorage::class),
            $loggerMock = Mockery::mock(LoggerInterface::class)
        );

        $storageMock
            ->expects('updateHistogram')
            ->andThrow(new StorageWriteException('Cannot write'));

        $loggerMock
            ->expects('error');

        $collector->update(42);
    }
}
