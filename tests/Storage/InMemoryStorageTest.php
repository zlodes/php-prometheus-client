<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Storage;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
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

        $storage->fetch();
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

    #[DataProvider('histogramDataProvider')]
    public function testHistogram(array $buckets, array $values, array $expectedFetched): void
    {
        $storage = new InMemoryStorage();

        $metricNameWithLabels = new MetricNameWithLabels('response_time');

        foreach ($values as $value) {
            $storage->persistHistogram(
                new MetricValue(
                    $metricNameWithLabels,
                    $value
                ),
                $buckets
            );
        }

        $fetched = $storage->fetch();
        self::assertSameSize($expectedFetched, $fetched);

        $actualFetched = [];

        foreach ($fetched as $metricValue) {
            $name = $metricValue->metricNameWithLabels->metricName;
            $labels = $metricValue->metricNameWithLabels->labels;
            $value = $metricValue->value;

            if (str_ends_with($name, '_sum')) {
                $actualFetched['sum'] = $value;

                continue;
            }

            if (str_ends_with($name, '_count')) {
                $actualFetched['count'] = $value;

                continue;
            }

            $actualFetched[$labels['le']] = $value;
        }

        self::assertEquals($expectedFetched, $actualFetched);
    }

    public static function histogramDataProvider(): iterable
    {
        yield 'all zeroes' => [
            [0, 1, 2, 3, 4],
            [0, 0, 0, 0, 0],
            [
                "0" => 5,
                "1" => 5,
                "2" => 5,
                "3" => 5,
                "4" => 5,
                "+Inf" => 5,
                "sum" => 0,
                "count" => 5,
            ]
        ];

        yield 'simple' => [
            [0, 1, 2, 3, 4, 5],
            [0, 1, 1, 2, 3, 3, 4, 5, 6],
            [
                "0" => 1,
                "1" => 3,
                "2" => 4,
                "3" => 6,
                "4" => 7,
                "5" => 8,
                "+Inf" => 9,
                "sum" => 25,
                "count" => 9,
            ]
        ];

        yield 'complex' => [
            [0, 1, 2, 3, 3.5, 5, 10],
            [1, 1, 1, 2, 0, 5, 7.5, 10, 30, 0.7, 53, 3.5, 4, 4, 8],
            [
                "0" => 1,
                "1" => 5,
                "2" => 6,
                "3" => 6,
                "3.5" => 7,
                "5" => 10,
                "10" => 13,
                "+Inf" => 15,
                "sum" => 130.7,
                "count" => 15,
            ]
        ];
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

        $storage->fetch();
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
