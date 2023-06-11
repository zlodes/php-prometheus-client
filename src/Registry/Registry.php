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
