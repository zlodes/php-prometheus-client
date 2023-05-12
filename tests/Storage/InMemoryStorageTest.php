<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Storage;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusExporter\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\DTO\MetricValue;
use Zlodes\PrometheusExporter\Exceptions\StorageReadException;
use Zlodes\PrometheusExporter\Normalization\Contracts\MetricKeyNormalizer;
use Zlodes\PrometheusExporter\Normalization\Exceptions\CannotNormalizeMetricsKey;
use Zlodes\PrometheusExporter\Storage\InMemoryStorage;
use Zlodes\PrometheusExporter\Storage\Storage;
use Zlodes\PrometheusExporter\Storage\StorageTesting;

class InMemoryStorageTest extends TestCase
{
    use StorageTesting;
    use MockeryPHPUnitIntegration;

    public function testKeyNormalizationError(): void
    {
        $keyNormalizerMock = Mockery::mock(MetricKeyNormalizer::class);

        $storage = new InMemoryStorage(
            metricKeyNormalizer: $keyNormalizerMock
        );

        $keyNormalizerMock
            ->expects('normalize')
            ->andThrow(new CannotNormalizeMetricsKey('Something went wrong'));

        $storage->setValue(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            )
        );

        $this->expectException(StorageReadException::class);

        $storage->fetch();
    }

    private function createStorage(): Storage
    {
        return new InMemoryStorage();
    }
}
