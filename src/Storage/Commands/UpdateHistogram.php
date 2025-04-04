<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Commands;

use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

final readonly class UpdateHistogram
{
    /**
     * @param non-empty-list<float> $buckets
     */
    public function __construct(
        public MetricNameWithLabels $metricNameWithLabels,
        public array $buckets,
        public int|float $value,
    ) {
    }
}
