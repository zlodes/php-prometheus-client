<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Collector;

use Psr\Log\LoggerInterface;
use Throwable;
use Zlodes\PrometheusExporter\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Normalization\Contracts\MetricKeyDenormalizer;
use Zlodes\PrometheusExporter\Registry\Registry;
use Zlodes\PrometheusExporter\Storage\Storage;

final class PersistentCollector implements Collector
{
    public function __construct(
        private readonly Registry $registry,
        private readonly Storage $storage,
        private readonly MetricKeyDenormalizer $metricsKeyDenormalizer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function counterIncrement(string $counterName, array $labels = [], float|int $value = 1): void
    {
        $counter = $this->registry->getCounter($counterName);

        if ($counter === null) {
            $this->logger->alert("Counter $counterName not found", [
                'labels' => $labels,
            ]);

            return;
        }

        $labels = array_merge($counter->getInitialLabels(), $labels);

        try {
            $key = $this->metricsKeyDenormalizer->denormalize(
                new MetricNameWithLabels($counterName, $labels)
            );

            $this->storage->incrementValue($key, $value);
        } catch (Throwable $e) {
            $this->logger->error("Cannot increment counter: $e");
        }
    }

    public function gaugeIncrement(string $gaugeName, array $labels = [], float|int $value = 1.0): void
    {
        $gauge = $this->registry->getGauge($gaugeName);

        if ($gauge === null) {
            $this->logger->alert("Gauge $gaugeName not found", [
                'labels' => $labels,
            ]);

            return;
        }

        $labels = array_merge($gauge->getInitialLabels(), $labels);

        try {
            $key = $this->metricsKeyDenormalizer->denormalize(
                new MetricNameWithLabels($gaugeName, $labels)
            );

            $this->storage->incrementValue($key, $value);
        } catch (Throwable $e) {
            $this->logger->error("Cannot increment gauge: $e");
        }
    }

    public function gaugeSet(string $gaugeName, array $labels = [], float|int $value = 1): void
    {
        $gauge = $this->registry->getGauge($gaugeName);

        if ($gauge === null) {
            $this->logger->alert("Gauge $gaugeName not found", [
                'labels' => $labels,
            ]);

            return;
        }

        $labels = array_merge($gauge->getInitialLabels(), $labels);

        try {
            $key = $this->metricsKeyDenormalizer->denormalize(
                new MetricNameWithLabels($gaugeName, $labels)
            );

            $this->storage->setValue($key, $value);
        } catch (Throwable $e) {
            $this->logger->error("Cannot set value of gauge: $e");
        }
    }
}
