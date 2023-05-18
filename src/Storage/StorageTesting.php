<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

use Zlodes\PrometheusExporter\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue; // phpcs:ignore
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

trait StorageTesting
{
    public function testGetSet(): void
    {
        $storage = $this->createStorage();

        assertEmpty($storage->fetch());

        $storage->setValue(
            new MetricValue(
                new MetricNameWithLabels('cpu_temp', ['core' => '0']),
                60
            )
        );

        $storage->setValue(
            new MetricValue(
                new MetricNameWithLabels('cpu_temp', ['core' => '1']),
                62
            )
        );

        $storage->incrementValue(
            new MetricValue(
                new MetricNameWithLabels('system_restarts_total'),
                1
            )
        );

        $storage->incrementValue(
            new MetricValue(
                new MetricNameWithLabels('system_restarts_total'),
                1
            )
        );

        $storage->setValue(
            new MetricValue(
                new MetricNameWithLabels('days_left'),
                10
            )
        );

        $storage->incrementValue(
            new MetricValue(
                new MetricNameWithLabels('days_left'),
                -1
            )
        );

        $fetched = $storage->fetch();
        $expected = [
            new MetricValue(
                new MetricNameWithLabels('cpu_temp', ['core' => '0']),
                60
            ),
            new MetricValue(
                new MetricNameWithLabels('cpu_temp', ['core' => '1']),
                62
            ),
            new MetricValue(
                new MetricNameWithLabels('system_restarts_total'),
                2
            ),
            new MetricValue(
                new MetricNameWithLabels('days_left'),
                9
            ),
        ];

        assertEquals($expected, $fetched);
    }

    public function testGetAllAndEmpty(): void
    {
        $storage = $this->createStorage();

        $storage->clear();
        assertEmpty($storage->fetch());

        $storage->setValue(
            new MetricValue(
                new MetricNameWithLabels('cpu_temp'),
                70
            )
        );

        assertCount(1, $storage->fetch());

        $storage->clear();
        assertEmpty($storage->fetch());
    }

    abstract protected function createStorage(): Storage;
}
