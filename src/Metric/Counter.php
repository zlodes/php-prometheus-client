<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Metric;

final class Counter extends BaseMetric
{
    public function getType(): MetricType
    {
        return MetricType::COUNTER;
    }
}
