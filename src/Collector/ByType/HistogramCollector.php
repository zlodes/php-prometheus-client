<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusClient\Collector\Collector;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\Storage;

/**
 * @final
 */
class HistogramCollector extends Collector
{
    /**
     * @internal Zlodes\PrometheusClient\Collector
     */
    public function __construct(
        private readonly Histogram $histogram,
        private readonly Storage $storage,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function update(float|int $value): void
    {
        $histogram = $this->histogram;
        $labels = $this->getLabels();
        $buckets = $this->histogram->getBuckets();

        try {
            $this->storage->persistHistogram(
                new MetricValue(
                    new MetricNameWithLabels($histogram->getName(), $labels),
                    $value,
                ),
                $buckets
            );
        } catch (StorageWriteException $e) {
            $this->logger->error("Cannot persist Histogram {$histogram->getName()}: $e");
        }
    }

    public function startTimer(): HistogramTimer
    {
        return new HistogramTimer($this);
    }
}
