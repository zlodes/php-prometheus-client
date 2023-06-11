<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusClient\Collector\ByType\CounterCollector;
use Zlodes\PrometheusClient\Collector\ByType\GaugeCollector;
use Zlodes\PrometheusClient\Collector\ByType\HistogramCollector;
use Zlodes\PrometheusClient\Exception\MetricHasWrongTypeException;
use Zlodes\PrometheusClient\Exception\MetricNotFoundException;
use Zlodes\PrometheusClient\Registry\Registry;
use Zlodes\PrometheusClient\Storage\Storage;

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
     * @throws MetricNotFoundException
     * @throws MetricHasWrongTypeException
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
     * @throws MetricNotFoundException
     * @throws MetricHasWrongTypeException
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
