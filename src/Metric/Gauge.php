<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Metric;

final class Gauge extends BaseMetric
{
    public function getType(): MetricType
    {
        return MetricType::GAUGE;
    }
}
