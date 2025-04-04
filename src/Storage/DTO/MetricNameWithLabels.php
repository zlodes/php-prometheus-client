<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\DTO;

final readonly class MetricNameWithLabels
{
    /**
     * @param non-empty-string $metricName
     * @param array<non-empty-string, non-empty-string> $labels
     */
    public function __construct(
        public string $metricName,
        public array $labels = [],
    ) {
    }
}
