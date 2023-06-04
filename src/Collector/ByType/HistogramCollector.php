<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Collector\ByType;

use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;
use Zlodes\PrometheusExporter\Collector\WithLabels;
use Zlodes\PrometheusExporter\Exceptions\StorageWriteException;
use Zlodes\PrometheusExporter\MetricTypes\Histogram;
use Zlodes\PrometheusExporter\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;
use Zlodes\PrometheusExporter\Storage\Storage;

final class HistogramCollector
{
    use WithLabels;

    public function __construct(
        private readonly Histogram $histogram,
        private readonly Storage $storage,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function update(float|int $value): void
    {
        Assert::true($value > 0, 'Value of Histogram metric MUST be positive');

        $histogram = $this->histogram;
        $labels = $this->composeLabels();
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

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function composeLabels(): array
    {
        return array_merge($this->histogram->getInitialLabels(), $this->labels);
    }
}
