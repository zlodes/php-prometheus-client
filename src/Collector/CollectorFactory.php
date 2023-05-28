<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Collector;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusExporter\Collector\ByType\CounterCollector;
use Zlodes\PrometheusExporter\Collector\ByType\GaugeCollector;
use Zlodes\PrometheusExporter\Collector\ByType\HistogramCollector;
use Zlodes\PrometheusExporter\Exceptions\MetricHasWrongType;
use Zlodes\PrometheusExporter\Exceptions\MetricNotFound;
use Zlodes\PrometheusExporter\Registry\Registry;
use Zlodes\PrometheusExporter\Storage\Storage;

/**
 * @final
 */
class CollectorFactory
{
    final public function __construct(
        private readonly Registry $registry,
        private readonly Storage $storage,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param non-empty-string $counterName
     *
     * @return CounterCollector
     *
     * @throws MetricNotFound
     * @throws MetricHasWrongType
     */
    final public function counter(string $counterName): CounterCollector
    {
        $counter = $this->registry->getCounter($counterName);

        return new CounterCollector(
            $counter,
            $this->storage,
            $this->logger
        );
    }

    /**
     * @param non-empty-string $gaugeName
     *
     * @return GaugeCollector
     *
     * @throws MetricNotFound
     * @throws MetricHasWrongType
     */
    final public function gauge(string $gaugeName): GaugeCollector
    {
        $gauge = $this->registry->getGauge($gaugeName);

        return new GaugeCollector(
            $gauge,
            $this->storage,
            $this->logger
        );
    }

    final public function histogram(string $histogramName): HistogramCollector
    {
        $histogram = $this->registry->getHistogram($histogramName);

        return new HistogramCollector(
            $histogram,
            $this->storage,
            $this->logger
        );
    }
}
