<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Commands;

use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

final readonly class UpdateGauge
{
    public function __construct(
        public MetricNameWithLabels $metricNameWithLabels,
        public int|float $value,
    ) {
    }
}
