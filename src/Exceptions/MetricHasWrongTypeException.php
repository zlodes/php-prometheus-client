<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Exceptions;

use Zlodes\PrometheusExporter\MetricTypes\MetricType;

final class MetricHasWrongTypeException extends MetricsException
{
    public function __construct(MetricType $expected, MetricType $actual)
    {
        parent::__construct(
            "Received metric with wrong type. Expected {$expected->value}, but {$actual->value} given"
        );
    }
}
