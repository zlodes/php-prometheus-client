<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Exporter;

use Generator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Throwable;
use Zlodes\PrometheusClient\Exporter\FetcherExporter;
use Zlodes\PrometheusClient\Fetcher\DTO\FetchedMetric;
use Zlodes\PrometheusClient\Fetcher\Fetcher;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

final class ExporterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @throws Throwable
     */
    public function testExport(): void
    {
        $exporter = new FetcherExporter(
            $fetcherMock = Mockery::mock(Fetcher::class)
        );

        $fetcherMock
            ->expects('fetch')
            ->andReturnUsing(static function (): Generator {
                yield new FetchedMetric(
                    new Gauge('temperature', 'Temperature of Bob\'s body, Celsius'),
                    [
                        new MetricValue(
                            new MetricNameWithLabels('temperature', ['source' => 'armpit', 'side' => 'left']),
                            36.5
                        ),
                        new MetricValue(
                            new MetricNameWithLabels('temperature', ['source' => 'ass']),
                            37.2
                        ),
                    ]
                );

                yield new FetchedMetric(
                    new Counter('jumps', 'Number of Bob\'s jumps'),
                    [
                        new MetricValue(
                            new MetricNameWithLabels('jumps'),
                            42
                        )
                    ]
                );
            });

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
