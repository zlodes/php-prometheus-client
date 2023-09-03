<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusClient\Collector\Collector;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\Storage;

/**
 * @final
 */
class GaugeCollector extends Collector
{
    /**
     * @internal Zlodes\PrometheusClient\Collector
     */
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
        $labels = $this->getLabels();

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
        $labels = $this->getLabels();

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
}
