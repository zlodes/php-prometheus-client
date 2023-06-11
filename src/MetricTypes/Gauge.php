<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\MetricTypes;

final class Gauge extends BaseMetric
{
    public function getType(): MetricType
    {
        return MetricType::GAUGE;
    }
}
