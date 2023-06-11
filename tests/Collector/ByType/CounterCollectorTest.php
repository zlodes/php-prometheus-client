<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Collector\ByType;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Zlodes\PrometheusClient\Collector\ByType\CounterCollector;
use Zlodes\PrometheusClient\Exceptions\StorageWriteException;
use Zlodes\PrometheusClient\MetricTypes\Counter;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\Storage;

class CounterCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testIncrement(): void
    {
        $counter = new Counter(
            'mileage',
            'Mileage in kilometres',
            [
                'country' => 'US',
            ]
        );

        $collector = new CounterCollector(
            $counter,
            $storageMock = Mockery::mock(Storage::class),
            new NullLogger()
        );

        /** @var MetricValue $metricValue */
        $storageMock
            ->expects('incrementValue')
            ->with(Mockery::capture($metricValue));

        $collector
            ->withLabels([
                'vin' => '1FT7W212345656558',
            ])
            ->increment();

        $expectedLabels = [
            'country' => 'US',
            'vin' => '1FT7W212345656558',
        ];

        self::assertEquals(1, $metricValue->value);
        self::assertEquals('mileage', $metricValue->metricNameWithLabels->metricName);
        self::assertEquals($expectedLabels, $metricValue->metricNameWithLabels->labels);
    }

    public function testStorageError(): void
    {
        $counter = new Counter('mileage', 'Mileage in kilometres');

        $collector = new CounterCollector(
            $counter,
            $storageMock = Mockery::mock(Storage::class),
            $loggerMock = Mockery::mock(LoggerInterface::class),
        );

        $storageMock
            ->expects('incrementValue')
            ->andThrow(new StorageWriteException('Cannot write'));

        $loggerMock
            ->expects('error');

        $collector->increment();
    }
}
