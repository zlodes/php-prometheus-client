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
