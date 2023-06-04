<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Storage;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusExporter\Exceptions\MetricKeySerializationException;
use Zlodes\PrometheusExporter\Exceptions\MetricKeyUnserializationException;
use Zlodes\PrometheusExporter\Exceptions\StorageReadException;
use Zlodes\PrometheusExporter\Exceptions\StorageWriteException;
use Zlodes\PrometheusExporter\KeySerialization\Serializer;
use Zlodes\PrometheusExporter\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;
use Zlodes\PrometheusExporter\Storage\InMemoryStorage;
use Zlodes\PrometheusExporter\Storage\Storage;
use Zlodes\PrometheusExporter\Storage\StorageTesting;

class InMemoryStorageTest extends TestCase
{
    use StorageTesting;
    use MockeryPHPUnitIntegration;

    public function testKeyUnserializeError(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryStorage($keySerializerMock);

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

        $storage = new InMemoryStorage($keySerializerMock);

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

        $storage = new InMemoryStorage($keySerializerMock);

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

        $storage = new InMemoryStorage($keySerializerMock);

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

        $storage = new InMemoryStorage($keySerializerMock);

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

    protected function createStorage(): Storage
    {
        return new InMemoryStorage();
    }
}
