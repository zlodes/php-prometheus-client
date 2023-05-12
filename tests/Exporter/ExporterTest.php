<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Exporter;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Throwable;
use Zlodes\PrometheusExporter\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\DTO\MetricValue;
use Zlodes\PrometheusExporter\Exporter\StoredMetricsExporter;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\Registry\ArrayRegistry;
use Zlodes\PrometheusExporter\Storage\Storage;

final class ExporterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @throws Throwable
     */
    public function testExport(): void
    {
        $exporter = new StoredMetricsExporter(
            $registry = new ArrayRegistry(),
            $storageMock = Mockery::mock(Storage::class),
            new NullLogger(),
        );

        $registry->registerMetric(
            new Gauge('temperature', 'Temperature of Bob\'s body, Celsius')
        );

        $registry->registerMetric(
            new Counter('jumps', 'Number of Bob\'s jumps')
        );

        $storageMock
            ->expects('fetch')
            ->andReturn([
                new MetricValue(
                    new MetricNameWithLabels('temperature', ['source' => 'armpit', 'side' => 'left']),
                    36.5
                ),
                new MetricValue(
                    new MetricNameWithLabels('temperature', ['source' => 'ass']),
                    37.2
                ),
            ]);

        $exportedStrings = [];

        foreach ($exporter->export() as $string) {
            $exportedStrings[] = $string;
        }

        $expectedFirst = <<<EOF
# HELP temperature Temperature of Bob's body, Celsius
# TYPE temperature gauge
temperature{source="armpit",side="left"} 36.5
temperature{source="ass"} 37.2
EOF;

        $expectedSecond = <<<EOF
# HELP jumps Number of Bob's jumps
# TYPE jumps counter
jumps 0
EOF;

        self::assertEquals($expectedFirst, $exportedStrings[0]);
        self::assertEquals($expectedSecond, $exportedStrings[1]);
    }
}
