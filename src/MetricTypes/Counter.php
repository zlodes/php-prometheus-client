<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\MetricTypes;

final class Counter extends BaseMetric
{
    public function getType(): MetricType
    {
        return MetricType::COUNTER;
    }
}
