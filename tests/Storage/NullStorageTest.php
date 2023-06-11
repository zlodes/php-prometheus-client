<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Storage;

use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\NullStorage;
use PHPUnit\Framework\TestCase;

class NullStorageTest extends TestCase
{
    public function testAllTheMethodsDoNothing(): void
    {
        $storage = new NullStorage();

        $storage->clear();
        self::assertSame([], $storage->fetch());

        $storage->setValue(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            )
        );

        $storage->incrementValue(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            )
        );

        $storage->persistHistogram(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            ),
            [1, 2, 3]
        );

        $storage->persistSummary(
            new MetricValue(
                new MetricNameWithLabels('foo', []),
                1
            )
        );

        self::assertSame([], $storage->fetch());
    }
}
