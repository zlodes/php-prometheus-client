<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\MetricTypes;

use Zlodes\PrometheusExporter\Enum\MetricType;

final class Counter extends SimpleMetric
{
    public function getType(): MetricType
    {
        return MetricType::COUNTER;
    }
}
