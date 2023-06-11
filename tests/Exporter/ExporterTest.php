<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Exporter;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Throwable;
use Zlodes\PrometheusClient\Exporter\StoredMetricsExporter;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Registry\ArrayRegistry;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\Storage;

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
                new MetricValue(
                    new MetricNameWithLabels('jumps'),
                    42
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
jumps 42
EOF;

        self::assertEquals($expectedFirst, $exportedStrings[0]);
        self::assertEquals($expectedSecond, $exportedStrings[1]);
    }
}
