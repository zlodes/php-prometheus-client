<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\Commands;

use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

final class UpdateHistogram
{
    /**
     * @param non-empty-list<float> $buckets
     */
    public function __construct(
        public readonly MetricNameWithLabels $metricNameWithLabels,
        public readonly array $buckets,
        public readonly int|float $value,
    ) {
    }
}
