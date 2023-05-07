<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Normalization;

use JsonException;
use Zlodes\PrometheusExporter\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Normalization\Contracts\MetricKeyDenormalizer;
use Zlodes\PrometheusExporter\Normalization\Exceptions\CannotDenormalizeMetricsKey;

final class JsonMetricKeyDenormalizer implements MetricKeyDenormalizer
{
    public function denormalize(MetricNameWithLabels $metricNameWithLabels): string
    {
        $name = $metricNameWithLabels->metricName;
        $labels = $metricNameWithLabels->labels;

        if ($labels === []) {
            return $name;
        }

        ksort($labels);

        try {
            $labelsString = json_encode($labels, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new CannotDenormalizeMetricsKey("JSON encoding error", previous: $e);
        }

        return $name . '|' . $labelsString;
    }
}
