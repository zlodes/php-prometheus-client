<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Exceptions;

final class MetricAlreadyRegistered extends MetricsException
{
    public function __construct(public readonly string $metricName)
    {
        parent::__construct("Metric with name $metricName has already registered");
    }
}
