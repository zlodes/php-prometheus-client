<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\DTO;

final readonly class MetricValue
{
    public function __construct(
        public MetricNameWithLabels $metricNameWithLabels,
        public int|float $value,
    ) {
    }
}
