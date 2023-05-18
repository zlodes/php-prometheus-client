<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\MetricTypes;

final class Counter extends SimpleMetric
{
    public function getType(): MetricType
    {
        return MetricType::COUNTER;
    }
}
