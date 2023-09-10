<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\DTO;

final class SummaryMetricValue
{
    /**
     * @param non-empty-list<int|float> $elements
     */
    public function __construct(
        public readonly MetricNameWithLabels $metricNameWithLabels,
        public readonly array $elements,
    ) {
    }
}
