<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage\DTO;

use Webmozart\Assert\Assert;

final class MetricNameWithLabels
{
    /**
     * @param non-empty-string $metricName
     * @param array<non-empty-string, non-empty-string> $labels
     */
    public function __construct(
        public readonly string $metricName,
        public readonly array $labels = [],
    ) {
        Assert::allString(array_keys($labels));
        Assert::allStringNotEmpty($labels);
    }
}
