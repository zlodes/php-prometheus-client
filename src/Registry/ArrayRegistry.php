<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Registry;

use Zlodes\PrometheusClient\Exception\MetricAlreadyRegisteredException;
use Zlodes\PrometheusClient\Exception\MetricHasWrongTypeException;
use Zlodes\PrometheusClient\Exception\MetricNotFoundException;
use Zlodes\PrometheusClient\Metric\Metric;

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
        $name = $metric->name;

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

    public function getMetric(string $name, string $class): Metric
    {
        $metric = $this->metrics[$name] ?? throw new MetricNotFoundException("Metric $name is not registered");

        if (is_a($metric, $class) === false) {
            throw new MetricHasWrongTypeException($class, $metric::class);
        }

        return $metric;
    }
}
