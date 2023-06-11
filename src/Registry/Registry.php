<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Registry;

use Zlodes\PrometheusClient\Exception\MetricAlreadyRegisteredException;
use Zlodes\PrometheusClient\Exception\MetricHasWrongTypeException;
use Zlodes\PrometheusClient\Exception\MetricNotFoundException;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Metric\Histogram;
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
     * @throws MetricNotFoundException Will be thrown when a metric with specified name isn't registered
     * @throws MetricHasWrongTypeException Will be thrown when found metric has unexpected type (e.g. Gauge)
     */
    public function getCounter(string $name): Counter;

    /**
     * @throws MetricNotFoundException Will be thrown when a metric with specified name isn't registered
     * @throws MetricHasWrongTypeException Will be thrown when found metric has unexpected type (e.g. Counter)
     */
    public function getGauge(string $name): Gauge;

    /**
     * @throws MetricNotFoundException Will be thrown when a metric with specified name isn't registered
     * @throws MetricHasWrongTypeException Will be thrown when found metric has unexpected type (e.g. Counter)
     */
    public function getHistogram(string $name): Histogram;
}
