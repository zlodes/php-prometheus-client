<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Registry;

use Zlodes\PrometheusClient\Exception\MetricAlreadyRegisteredException;
use Zlodes\PrometheusClient\Exception\MetricHasWrongTypeException;
use Zlodes\PrometheusClient\Exception\MetricNotFoundException;
use Zlodes\PrometheusClient\Metric\Metric;

interface Registry
{
    /**
     * @return $this
     *
     * @throws MetricAlreadyRegisteredException
     */
    public function registerMetric(Metric $metric): self;

    /**
     * @return array<string, Metric> Name => Metric
     */
    public function getAll(): array;

    /**
     * @template TMetric of Metric
     *
     * @param non-empty-string $name
     * @param class-string<TMetric> $class
     *
     * @return TMetric
     *
     * @throws MetricNotFoundException When a metric with specified name isn't registered
     * @throws MetricHasWrongTypeException When found metric has different type (e.g. expected Counter but Gauge given)
     */
    public function getMetric(string $name, string $class): Metric;
}
