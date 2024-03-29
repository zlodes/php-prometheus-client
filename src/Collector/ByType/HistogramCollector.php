<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusClient\Collector\Collector;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\StopWatch\HRTimeStopWatch;
use Zlodes\PrometheusClient\StopWatch\StopWatch;
use Zlodes\PrometheusClient\Storage\Commands\UpdateHistogram;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

/**
 * @final
 */
class HistogramCollector extends Collector
{
    /**
     * @internal Zlodes\PrometheusClient\Collector
     *
     * @param class-string<StopWatch> $stopWatchClass
     */
    public function __construct(
        private readonly Histogram $histogram,
        private readonly HistogramStorage $storage,
        private readonly LoggerInterface $logger,
        private readonly string $stopWatchClass = HRTimeStopWatch::class,
    ) {
    }

    public function update(float|int $value): void
    {
        $histogram = $this->histogram;
        $labels = $this->getLabels();
        $buckets = $this->histogram->getBuckets();

        try {
            $this->storage->updateHistogram(
                new UpdateHistogram(
                    new MetricNameWithLabels($histogram->name, $labels),
                    $buckets,
                    $value
                )
            );
        } catch (StorageWriteException $e) {
            $this->logger->error("Cannot persist Histogram {$histogram->name}: $e");
        }
    }

    public function startTimer(): StopWatch
    {
        $stopWatchClass = $this->stopWatchClass;

        return new $stopWatchClass(function (float $elapsed): void {
            $this->update($elapsed);
        });
    }
}
