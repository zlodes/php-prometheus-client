<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Collector\ByType;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusExporter\Exceptions\StorageWriteException;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;
use Zlodes\PrometheusExporter\Storage\Storage;

final class GaugeCollector
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $labels = [];

    public function __construct(
        private readonly Gauge $gauge,
        private readonly Storage $storage,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param non-empty-array<non-empty-string, non-empty-string> $labels
     *
     * @return self
     */
    public function withLabels(array $labels): self
    {
        $instance = clone $this;
        $instance->labels = $labels;

        return $instance;
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

    public function setValue(int|float $value): void
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
