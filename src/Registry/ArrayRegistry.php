<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Registry;

use Zlodes\PrometheusExporter\Exceptions\MetricAlreadyRegisteredException;
use Zlodes\PrometheusExporter\Exceptions\MetricHasWrongTypeException;
use Zlodes\PrometheusExporter\Exceptions\MetricNotFoundException;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\MetricTypes\Histogram;
use Zlodes\PrometheusExporter\MetricTypes\Metric;
use Zlodes\PrometheusExporter\MetricTypes\MetricType;

final class ArrayRegistry implements Registry
{
    /** @var array<non-empty-string, Metric> */
    private array $metrics = [];

    /**
     * @return $this
     *
     * @throws MetricAlreadyRegisteredException
     */
    public function registerMetric(Metric $metric): self
    {
        $name = $metric->getName();

        if (array_key_exists($name, $this->metrics)) {
            throw new MetricAlreadyRegisteredException($name);
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
        $metric = $this->getMetric($name) ?? throw new MetricNotFoundException("Metric $name is not registered");

        if (!$metric instanceof Counter) {
            throw new MetricHasWrongTypeException(MetricType::COUNTER, $metric->getType());
        }

        return $metric;
    }

    public function getGauge(string $name): Gauge
    {
        $metric = $this->getMetric($name) ?? throw new MetricNotFoundException("Metric $name is not registered");

        if (!$metric instanceof Gauge) {
            throw new MetricHasWrongTypeException(MetricType::GAUGE, $metric->getType());
        }

        return $metric;
    }

    public function getHistogram(string $name): Histogram
    {
        $metric = $this->getMetric($name) ?? throw new MetricNotFoundException("Metric $name is not registered");

        if (!$metric instanceof Histogram) {
            throw new MetricHasWrongTypeException(MetricType::HISTOGRAM, $metric->getType());
        }

        return $metric;
    }
}
