<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Collector\ByType;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Zlodes\PrometheusClient\Collector\ByType\CounterCollector;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Storage\Commands\IncrementCounter;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;

class CounterCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testIncrement(): void
    {
        $counter = new Counter(
            'mileage',
            'Mileage in kilometres',
        );

        $collector = new CounterCollector(
            $counter,
            $storageMock = Mockery::mock(CounterStorage::class),
            new NullLogger()
        );

        /** @var IncrementCounter $incrementCommand */
        $storageMock
            ->expects('incrementCounter')
            ->with(Mockery::capture($incrementCommand));

        $collector
            ->withLabels([
                'vin' => '1FT7W212345656558',
            ])
            ->increment();

        $expectedLabels = [
            'vin' => '1FT7W212345656558',
        ];

        self::assertEquals(1, $incrementCommand->value);
        self::assertEquals('mileage', $incrementCommand->metricNameWithLabels->metricName);
        self::assertEquals($expectedLabels, $incrementCommand->metricNameWithLabels->labels);
    }

    public function testStorageError(): void
    {
        $counter = new Counter('mileage', 'Mileage in kilometres');

        $collector = new CounterCollector(
            $counter,
            $storageMock = Mockery::mock(CounterStorage::class),
            $loggerMock = Mockery::mock(LoggerInterface::class),
        );

        $storageMock
            ->expects('incrementCounter')
            ->andThrow(new StorageWriteException('Cannot write'));

        $loggerMock
            ->expects('error');

        $collector->increment();
    }

    #[DataProvider('badIncrementValuesDataProvider')]
    public function testIncrementWithBadValues(int $value): void
    {
        $collector = new CounterCollector(
            new Counter('mileage', 'Mileage in kilometres'),
            Mockery::mock(CounterStorage::class),
            new NullLogger()
        );

        $this->expectException(InvalidArgumentException::class);

        $collector->increment($value);
    }

    public static function badIncrementValuesDataProvider(): iterable
    {
        yield 'negative' => [-1];
        yield 'zero' => [0];
    }
}
