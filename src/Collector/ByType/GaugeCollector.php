<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusClient\Collector\WithLabels;
use Zlodes\PrometheusClient\Exceptions\StorageWriteException;
use Zlodes\PrometheusClient\MetricTypes\Gauge;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\Storage;

final class GaugeCollector
{
    use WithLabels;

    public function __construct(
        private readonly Gauge $gauge,
        private readonly Storage $storage,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param positive-int|float $value
     *
     * @return void
     */
    public function increment(int|float $value = 1): void
    {
        $gauge = $this->gauge;
        $labels = $this->composeLabels();

        try {
            $this->storage->incrementValue(
                new MetricValue(
                    new MetricNameWithLabels($gauge->getName(), $labels),
                    $value
                )
            );
        } catch (StorageWriteException $e) {
            $this->logger->error("Cannot increment gauge {$gauge->getName()}: $e");
        }
    }

    public function update(int|float $value): void
    {
        $gauge = $this->gauge;
        $labels = $this->composeLabels();

        try {
            $this->storage->setValue(
                new MetricValue(
                    new MetricNameWithLabels($gauge->getName(), $labels),
                    $value
                )
            );
        } catch (StorageWriteException $e) {
            $this->logger->error("Cannot set value of gauge {$gauge->getName()}: $e");
        }
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function composeLabels(): array
    {
        return array_merge($this->gauge->getInitialLabels(), $this->labels);
    }
}
