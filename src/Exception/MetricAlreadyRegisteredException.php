<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Exception;

final class MetricAlreadyRegisteredException extends MetricsException
{
    public function __construct(public readonly string $metricName)
    {
        parent::__construct("Metric with name $metricName has already registered");
    }
}
