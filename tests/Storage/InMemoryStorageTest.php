<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Storage;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Registry\ArrayRegistry;
use Zlodes\PrometheusClient\Registry\Registry;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\InMemory\InMemoryStorage;
use Zlodes\PrometheusClient\Storage\Storage;
use Zlodes\PrometheusClient\Storage\StorageTesting;

class InMemoryStorageTest extends TestCase
{
    use StorageTesting;
    use MockeryPHPUnitIntegration;

    public function testKeyUnserializeError(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryStorage(new ArrayRegistry(), $keySerializerMock);

        $keySerializerMock
            ->expects('serialize');

        $storage->setValue(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            )
        );

        $keySerializerMock
            ->expects('unserialize')
            ->andThrow(
                new MetricKeyUnserializationException('Something went wrong')
            );

        $this->expectException(StorageReadException::class);
        $this->expectExceptionMessage('Cannot unserialize metrics key');

        iterator_to_array($storage->fetch());
    }

    public function testSetValueError(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryStorage(new ArrayRegistry(), $keySerializerMock);

        $keySerializerMock
            ->expects('serialize')
            ->andThrow(
                new MetricKeySerializationException('Something went wrong')
            );

        $this->expectException(StorageWriteException::class);
        $this->expectExceptionMessage('Cannot serialize metric key');

        $storage->setValue(
            new MetricValue(
                new MetricNameWithLabels(
                    'foo'
                ),
                42
            )
        );
    }

    public function testIncrementValueError(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryStorage(new ArrayRegistry(), $keySerializerMock);

        $keySerializerMock
            ->expects('serialize')
            ->andThrow(
                new MetricKeySerializationException('Something went wrong')
            );

        $this->expectException(StorageWriteException::class);
        $this->expectExceptionMessage('Cannot serialize metric key');

        $storage->incrementValue(
            new MetricValue(
                new MetricNameWithLabels(
                    'foo'
                ),
                42
            )
        );
    }

    public function testKeyUnserializeErrorWhileFetchingHistogram(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryStorage(new ArrayRegistry(), $keySerializerMock);

        $keySerializerMock
            ->expects('serialize');

        $storage->persistHistogram(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            ),
            [0, 1, 2, 3]
        );

        $keySerializerMock
            ->expects('unserialize')
            ->andThrow(
                new MetricKeyUnserializationException('Something went wrong')
            );

        $this->expectException(StorageReadException::class);
        $this->expectExceptionMessage('Cannot unserialize metrics key');

        iterator_to_array($storage->fetch());
    }

    public function testSerializationExceptionWhilePersistingHistogram(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryStorage(new ArrayRegistry(), $keySerializerMock);

        $keySerializerMock
            ->expects('serialize')
            ->andThrow(
                new MetricKeySerializationException('Something went wrong')
            );

        $this->expectException(StorageWriteException::class);
        $this->expectExceptionMessage('Cannot serialize metric key');

        $storage->persistHistogram(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            ),
            [0, 1, 2, 3]
        );
    }

    public function testKeyUnserializeErrorWhileFetchingSummary(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryStorage(new ArrayRegistry(), $keySerializerMock);

        $keySerializerMock
            ->expects('serialize');

        $storage->persistSummary(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            )
        );

        $keySerializerMock
            ->expects('unserialize')
            ->andThrow(
                new MetricKeyUnserializationException('Something went wrong')
            );

        $this->expectException(StorageReadException::class);
        $this->expectExceptionMessage('Cannot unserialize metrics key');

        iterator_to_array($storage->fetch());
    }

    public function testSerializationExceptionWhilePersistingSummary(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryStorage(new ArrayRegistry(), $keySerializerMock);

        $keySerializerMock
            ->expects('serialize')
            ->andThrow(
                new MetricKeySerializationException('Something went wrong')
            );

        $this->expectException(StorageWriteException::class);
        $this->expectExceptionMessage('Cannot serialize metric key');

        $storage->persistSummary(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            )
        );
    }

    protected function createStorage(Registry $registry = new ArrayRegistry()): Storage
    {
        return new InMemoryStorage($registry);
    }
}
