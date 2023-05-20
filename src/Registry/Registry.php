<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Registry;

use Zlodes\PrometheusExporter\Exceptions\MetricAlreadyRegistered;
use Zlodes\PrometheusExporter\Exceptions\MetricHasWrongType;
use Zlodes\PrometheusExporter\Exceptions\MetricNotFound;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\MetricTypes\Metric;

interface Registry
{
    /**
     * @return $this
     *
     * @throws MetricAlreadyRegistered
     */
    public function registerMetric(Metric $metric): self;

    /**
     * @return iterable<string, Metric> Name => Metric
     */
    public function getAll(): iterable;

    /**
     * @throws MetricNotFound Will be thrown when a metric with specified name isn't registered
     * @throws MetricHasWrongType Will be thrown when found metric has unexpected type (e.g. Gauge)
     */
    public function getCounter(string $name): Counter;

    /**
     * @throws MetricNotFound Will be thrown when a metric with specified name isn't registered
     * @throws MetricHasWrongType Will be thrown when found metric has unexpected type (e.g. Counter)
     */
    public function getGauge(string $name): Gauge;
}
