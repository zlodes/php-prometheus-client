<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\DTO;

use Webmozart\Assert\Assert;

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
        Assert::allString(array_keys($labels));
        Assert::allStringNotEmpty($labels);
    }
}
