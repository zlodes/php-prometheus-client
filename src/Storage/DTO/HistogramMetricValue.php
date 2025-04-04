<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\DTO;

final readonly class HistogramMetricValue
{
    /**
     * @param non-empty-array<non-empty-string|positive-int, int|float> $buckets
     */
    public function __construct(
        public MetricNameWithLabels $metricNameWithLabels,
        public array $buckets,
        public float $sum,
        public int $count,
    ) {
    }
}
