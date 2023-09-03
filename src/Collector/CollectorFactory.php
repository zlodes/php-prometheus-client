<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusClient\Collector\ByType\CounterCollector;
use Zlodes\PrometheusClient\Collector\ByType\GaugeCollector;
use Zlodes\PrometheusClient\Collector\ByType\HistogramCollector;
use Zlodes\PrometheusClient\Collector\ByType\SummaryCollector;
use Zlodes\PrometheusClient\Exception\MetricHasWrongTypeException;
use Zlodes\PrometheusClient\Exception\MetricNotFoundException;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Metric\Summary;
use Zlodes\PrometheusClient\Registry\Registry;
use Zlodes\PrometheusClient\Storage\Storage;

/**
 * @final
 */
class CollectorFactory
{
    public function __construct(
        private readonly Registry $registry,
        private readonly Storage $storage,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @final
     *
     * @param non-empty-string $counterName
     *
     * @return CounterCollector
     *
     * @throws MetricNotFoundException
     * @throws MetricHasWrongTypeException
     */
    public function counter(string $counterName): CounterCollector
    {
        $counter = $this->registry->getMetric($counterName, Counter::class);

        return new CounterCollector(
            $counter,
            $this->storage,
            $this->logger
        );
    }

    /**
     * @final
     *
     * @param non-empty-string $gaugeName
     *
     * @return GaugeCollector
     *
     * @throws MetricNotFoundException
     * @throws MetricHasWrongTypeException
     */
    public function gauge(string $gaugeName): GaugeCollector
    {
        $gauge = $this->registry->getMetric($gaugeName, Gauge::class);

        return new GaugeCollector(
            $gauge,
            $this->storage,
            $this->logger
        );
    }

    /**
     * @final
     *
     * @param non-empty-string $histogramName
     *
     * @return HistogramCollector
     *
     * @throws MetricNotFoundException
     * @throws MetricHasWrongTypeException
     */
    public function histogram(string $histogramName): HistogramCollector
    {
        $histogram = $this->registry->getMetric($histogramName, Histogram::class);

        return new HistogramCollector(
            $histogram,
            $this->storage,
            $this->logger
        );
    }

    /**
     * @param non-empty-string $summaryName
     *
     * @return SummaryCollector
     *
     * @throws MetricNotFoundException
     * @throws MetricHasWrongTypeException
     */
    final public function summary(string $summaryName): SummaryCollector
    {
        $summary = $this->registry->getMetric($summaryName, Summary::class);

        return new SummaryCollector(
            $summary,
            $this->storage,
            $this->logger
        );
    }
}
