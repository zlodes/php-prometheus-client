<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\MetricTypes;

final class Gauge extends SimpleMetric
{
    public function getType(): MetricType
    {
        return MetricType::GAUGE;
    }
}
