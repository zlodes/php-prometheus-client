<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Registry;

use Generator;
use Zlodes\PrometheusExporter\Exceptions\MetricAlreadyRegistered;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\MetricTypes\Metric;

final class ArrayRegistry implements Registry
{
    /** @var array<non-empty-string, Metric> */
    private array $metrics = [];

    /**
     * @return $this
     *
     * @throws MetricAlreadyRegistered
     */
    public function registerMetric(Metric $metric): self
    {
        $name = $metric->getName();

        if (array_key_exists($name, $this->metrics)) {
            throw new MetricAlreadyRegistered($name);
        }

        $this->metrics[$name] = $metric;

        return $this;
    }

    public function getAll(): Generator
    {
        yield from $this->metrics;
    }

    public function getMetric(string $name): ?Metric
    {
        return $this->metrics[$name] ?? null;
    }

    public function getCounter(string $name): ?Counter
    {
        $metric = $this->getMetric($name);

        if ($metric instanceof Counter) {
            return $metric;
        }

        return null;
    }

    public function getGauge(string $name): ?Gauge
    {
        $metric = $this->getMetric($name);

        if ($metric instanceof Gauge) {
            return $metric;
        }

        return null;
    }
}
