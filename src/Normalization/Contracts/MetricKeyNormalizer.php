<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Normalization\Contracts;

use Zlodes\PrometheusExporter\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Normalization\Exceptions\CannotNormalizeMetricsKey;

interface MetricKeyNormalizer
{
    /**
     * @param non-empty-string $denormalized
     *
     * @throws CannotNormalizeMetricsKey
     */
    public function normalize(string $denormalized): MetricNameWithLabels;
}
