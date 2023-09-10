<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusClient\Collector\Collector;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Storage\Commands\UpdateGauge;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

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
        private readonly GaugeStorage $storage,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function update(int|float $value): void
    {
        $gauge = $this->gauge;
        $labels = $this->getLabels();

        try {
            $this->storage->updateGauge(
                new UpdateGauge(
                    new MetricNameWithLabels($gauge->name, $labels),
                    $value
                )
            );
        } catch (StorageWriteException $e) {
            $this->logger->error("Cannot set value of gauge {$gauge->name}: $e");
        }
    }
}
