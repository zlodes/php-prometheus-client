<?php

declare(strict_types=1);

namespace Storage\InMemory;

use Mockery;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Storage\Commands\IncrementCounter;
use Zlodes\PrometheusClient\Storage\Commands\UpdateGauge;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\InMemory\InMemoryCounterStorage;
use Zlodes\PrometheusClient\Storage\InMemory\InMemoryGaugeStorage;
use Zlodes\PrometheusClient\Storage\Testing\CounterStorageTesting;
use Zlodes\PrometheusClient\Storage\Testing\GaugeStorageTesting;

class InMemoryGaugeStorageTest extends TestCase
{
    use GaugeStorageTesting;

    public function testSerializationException(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryGaugeStorage($keySerializerMock);

        $keySerializerMock
            ->expects('serialize')
            ->andThrow(
                new MetricKeySerializationException('Something went wrong')
            );

        $this->expectException(StorageWriteException::class);
        $this->expectExceptionMessage('Cannot serialize metric key');

        $storage->updateGauge(
            new UpdateGauge(
                new MetricNameWithLabels(
                    'foo'
                ),
                42
            )
        );
    }

    public function testKeyUnserializeError(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryGaugeStorage($keySerializerMock);

        $keySerializerMock
            ->expects('serialize');

        $storage->updateGauge(
            new UpdateGauge(
                new MetricNameWithLabels('foo', []),
                42
            )
        );

        $keySerializerMock
            ->expects('unserialize')
            ->andThrow(
                new MetricKeyUnserializationException('Something went wrong')
            );

        $this->expectException(StorageReadException::class);
        $this->expectExceptionMessage('Cannot unserialize metrics key');

        [...$storage->fetchGauges()];
    }

    protected function createStorage(): GaugeStorage
    {
        return new InMemoryGaugeStorage();
    }
}
