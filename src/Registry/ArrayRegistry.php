<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Registry;

use Zlodes\PrometheusClient\Exceptions\MetricAlreadyRegisteredException;
use Zlodes\PrometheusClient\Exceptions\MetricHasWrongTypeException;
use Zlodes\PrometheusClient\Exceptions\MetricNotFoundException;
use Zlodes\PrometheusClient\MetricTypes\Counter;
use Zlodes\PrometheusClient\MetricTypes\Gauge;
use Zlodes\PrometheusClient\MetricTypes\Histogram;
use Zlodes\PrometheusClient\MetricTypes\Metric;
use Zlodes\PrometheusClient\MetricTypes\MetricType;

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
