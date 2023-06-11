<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;
use Zlodes\PrometheusClient\Collector\WithLabels;
use Zlodes\PrometheusClient\Exceptions\StorageWriteException;
use Zlodes\PrometheusClient\MetricTypes\Histogram;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\Storage;

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
