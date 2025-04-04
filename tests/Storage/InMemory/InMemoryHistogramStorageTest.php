<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Storage\InMemory;

use Mockery;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Storage\Commands\UpdateHistogram;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\InMemory\InMemoryHistogramStorage;
use Zlodes\PrometheusClient\Storage\Testing\HistogramStorageTesting;

final class InMemoryHistogramStorageTest extends TestCase
{
    use HistogramStorageTesting;

    public function testKeyUnserializeErrorWhileFetchingHistogram(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryHistogramStorage($keySerializerMock);

        $keySerializerMock
            ->expects('serialize');

        $storage->updateHistogram(
            new UpdateHistogram(
                new MetricNameWithLabels('foo', []),
                [0, 1, 2, 3],
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

        [...$storage->fetchHistograms()];
    }

    public function testSerializationExceptionWhilePersistingHistogram(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemoryHistogramStorage($keySerializerMock);

        $keySerializerMock
            ->expects('serialize')
            ->andThrow(
                new MetricKeySerializationException('Something went wrong')
            );

        $this->expectException(StorageWriteException::class);
        $this->expectExceptionMessage('Cannot serialize metric key');

        $storage->updateHistogram(
            new UpdateHistogram(
                new MetricNameWithLabels('foo', []),
                [0, 1, 2, 3],
                1
            )
        );
    }

    protected function createStorage(): HistogramStorage
    {
        return new InMemoryHistogramStorage();
    }
}
