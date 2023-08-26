<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Exception;

use Zlodes\PrometheusClient\Metric\Metric;

final class MetricHasWrongTypeException extends MetricsException
{
    /**
     * @param class-string<Metric> $expected
     * @param class-string<Metric> $actual
     */
    public function __construct(string $expected, string $actual)
    {
        parent::__construct(
            "Received metric with wrong type. Expected metric to be $expected, but $actual given"
        );
    }
}
