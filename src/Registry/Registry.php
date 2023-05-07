<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Registry;

use Zlodes\PrometheusExporter\Exceptions\MetricAlreadyRegistered;
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

    public function getCounter(string $name): ?Counter;

    public function getGauge(string $name): ?Gauge;
}
