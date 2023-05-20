<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Registry;

use Zlodes\PrometheusExporter\Exceptions\MetricAlreadyRegistered;
use Zlodes\PrometheusExporter\Exceptions\MetricHasWrongType;
use Zlodes\PrometheusExporter\Exceptions\MetricNotFound;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\MetricTypes\Metric;
use Zlodes\PrometheusExporter\MetricTypes\MetricType;

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

    public function getAll(): array
    {
        return $this->metrics;
    }

    public function getMetric(string $name): ?Metric
    {
        return $this->metrics[$name] ?? null;
    }

    public function getCounter(string $name): Counter
    {
        $metric = $this->getMetric($name) ?? throw new MetricNotFound("Metric $name is not registered");

        if (!$metric instanceof Counter) {
            throw new MetricHasWrongType(MetricType::COUNTER, $metric->getType());
        }

        return $metric;
    }

    public function getGauge(string $name): Gauge
    {
        $metric = $this->getMetric($name) ?? throw new MetricNotFound("Metric $name is not registered");

        if (!$metric instanceof Gauge) {
            throw new MetricHasWrongType(MetricType::GAUGE, $metric->getType());
        }

        return $metric;
    }
}
