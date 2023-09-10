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
use Zlodes\PrometheusClient\Metric\Summary;
use Zlodes\PrometheusClient\Storage\Commands\UpdateSummary;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

class SummaryCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testUpdate(): void
    {
        $summary = new Summary('response_time', 'App response time');

        $collector = new SummaryCollector(
            $summary,
            $storageMock = Mockery::mock(SummaryStorage::class),
            new NullLogger()
        );

        /** @var UpdateSummary $updateCommand */
        $storageMock
            ->expects('updateSummary')
            ->with(
                Mockery::capture($updateCommand)
            );

        $collector
            ->withLabels(['route' => '/'])
            ->update(0.65);

        self::assertEquals('response_time', $updateCommand->metricNameWithLabels->metricName);
        self::assertEquals(0.65, $updateCommand->value);
        self::assertEquals(['route' => '/'], $updateCommand->metricNameWithLabels->labels);
    }

    public function testTimer(): void
    {
        $summary = new Summary('response_time', 'App response time');

        $collector = new SummaryCollector(
            $summary,
            $storageMock = Mockery::mock(SummaryStorage::class),
            new NullLogger()
        );

        /** @var UpdateSummary $updateCommand */
        $storageMock
            ->expects('updateSummary')
            ->with(
                Mockery::capture($updateCommand)
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
        $summary = new Summary('response_time', 'App response time');

        $collector = new SummaryCollector(
            $summary,
            $storageMock = Mockery::mock(SummaryStorage::class),
            $loggerMock = Mockery::mock(LoggerInterface::class)
        );

        /** @var MetricValue $metricValue */
        $storageMock
            ->expects('updateSummary')
            ->andThrow(new StorageWriteException('Cannot write'));

        $loggerMock
            ->expects('error');

        $collector->update(42);
    }
}
