<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Collector;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusExporter\Collector\ByType\CounterCollector;
use Zlodes\PrometheusExporter\Collector\ByType\GaugeCollector;
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
     */
    final public function counter(string $counterName): CounterCollector
    {
        $counter = $this->registry->getCounter($counterName)
            ?? throw new MetricNotFound("Counter $counterName not found");

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
     */
    final public function gauge(string $gaugeName): GaugeCollector
    {
        $gauge = $this->registry->getGauge($gaugeName)
            ?? throw new MetricNotFound("Counter $gaugeName not found");

        return new GaugeCollector(
            $gauge,
            $this->storage,
            $this->logger
        );
    }
}
