<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\DTO;

final class HistogramMetricValue
{
    /**
     * @param non-empty-array<non-empty-string|positive-int, int|float> $buckets
     */
    public function __construct(
        public readonly MetricNameWithLabels $metricNameWithLabels,
        public readonly array $buckets,
        public readonly float $sum,
        public readonly int $count,
    ) {
    }
}
