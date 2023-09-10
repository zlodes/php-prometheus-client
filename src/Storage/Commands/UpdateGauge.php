<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Commands;

use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

final class UpdateGauge
{
    public function __construct(
        public readonly MetricNameWithLabels $metricNameWithLabels,
        public readonly int|float $value,
    ) {
    }
}
