<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\DTO;

final class MetricValue
{
    public function __construct(
        public readonly MetricNameWithLabels $metricNameWithLabels,
        public readonly int|float $value,
    ) {
    }
}
