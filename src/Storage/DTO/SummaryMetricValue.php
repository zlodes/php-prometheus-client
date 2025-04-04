<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\DTO;

final readonly class SummaryMetricValue
{
    /**
     * @param non-empty-list<int|float> $elements
     */
    public function __construct(
        public MetricNameWithLabels $metricNameWithLabels,
        public array $elements,
    ) {
    }
}
