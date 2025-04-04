<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Collector\ByType;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Zlodes\PrometheusClient\Collector\ByType\GaugeCollector;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Storage\Commands\UpdateGauge;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;

final class GaugeCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSetValue(): void
    {
        $gauge = new Gauge('body_temperature', 'Body temperature in Celsius');

        $collector = new GaugeCollector(
            $gauge,
            $storageMock = Mockery::mock(GaugeStorage::class),
            new NullLogger()
        );

        /** @var UpdateGauge $updateCommand */
        $storageMock
            ->expects('updateGauge')
            ->with(Mockery::capture($updateCommand));

        $collector
            ->withLabels([
                'source' => 'armpit',
            ])
            ->update(36.6);

        $expectedLabels = [
            'source' => 'armpit',
        ];

        self::assertEquals(36.6, $updateCommand->value);
        self::assertEquals('body_temperature', $updateCommand->metricNameWithLabels->metricName);
        self::assertEquals($expectedLabels, $updateCommand->metricNameWithLabels->labels);
    }


    public function testStorageErrorWhileSettingValue(): void
    {
        $gauge = new Gauge('score', 'Quiz game players score');

        $collector = new GaugeCollector(
            $gauge,
            $storageMock = Mockery::mock(GaugeStorage::class),
            $loggerMock = Mockery::mock(LoggerInterface::class)
        );

        $storageMock
            ->expects('updateGauge')
            ->andThrow(new StorageWriteException('Cannot write'));

        $loggerMock
            ->expects('error');

        $collector->update(42);
    }
}
