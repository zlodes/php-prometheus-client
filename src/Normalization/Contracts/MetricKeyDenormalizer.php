<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Normalization\Contracts;

use Zlodes\PrometheusExporter\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Normalization\Exceptions\CannotDenormalizeMetricsKey;

interface MetricKeyDenormalizer
{
    /**
     * @return non-empty-string
     *
     * @throws CannotDenormalizeMetricsKey
     */
    public function denormalize(MetricNameWithLabels $metricNameWithLabels): string;
}
