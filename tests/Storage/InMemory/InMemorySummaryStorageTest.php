<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Storage\InMemory;

use Mockery;
use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Storage\Commands\UpdateSummary;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\InMemory\InMemorySummaryStorage;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Storage\Testing\SummaryStorageTesting;

final class InMemorySummaryStorageTest extends TestCase
{
    use SummaryStorageTesting;

    protected function createStorage(): SummaryStorage
    {
        return new InMemorySummaryStorage();
    }

    public function testKeyUnserializeErrorWhileFetchingSummary(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemorySummaryStorage($keySerializerMock);

        $keySerializerMock
            ->expects('serialize');

        $storage->updateSummary(
            new UpdateSummary(
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

        [...$storage->fetchSummaries()];
    }

    public function testSerializationExceptionWhilePersistingSummary(): void
    {
        $keySerializerMock = Mockery::mock(Serializer::class);

        $storage = new InMemorySummaryStorage($keySerializerMock);

        $keySerializerMock
            ->expects('serialize')
            ->andThrow(
                new MetricKeySerializationException('Something went wrong')
            );

        $this->expectException(StorageWriteException::class);
        $this->expectExceptionMessage('Cannot serialize metric key');

        $storage->updateSummary(
            new UpdateSummary(
                new MetricNameWithLabels('foo', []),
                1
            )
        );
    }
}
