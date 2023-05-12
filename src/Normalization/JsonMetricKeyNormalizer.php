<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Normalization;

use InvalidArgumentException;
use JsonException;
use Webmozart\Assert\Assert;
use Zlodes\PrometheusExporter\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Normalization\Contracts\MetricKeyNormalizer;
use Zlodes\PrometheusExporter\Normalization\Exceptions\CannotNormalizeMetricsKey;

final class JsonMetricKeyNormalizer implements MetricKeyNormalizer
{
    public function normalize(string $denormalized): MetricNameWithLabels
    {
        try {
            $nameWithLabelsRaw = explode('|', $denormalized, 2);
            Assert::notEmpty($denormalized);
            Assert::countBetween($nameWithLabelsRaw, 1, 2);

            $name = $nameWithLabelsRaw[0];
            Assert::notEmpty($name);

            $labels = array_key_exists(1, $nameWithLabelsRaw)
                ? json_decode($nameWithLabelsRaw[1], true, 2, JSON_THROW_ON_ERROR)
                : [];

            Assert::isArray($labels);
            Assert::allStringNotEmpty($labels);
            /** @psalm-var array<non-empty-string, non-empty-string> $labels */

            return new MetricNameWithLabels($name, $labels);
        } catch (JsonException | InvalidArgumentException $e) {
            throw new CannotNormalizeMetricsKey(
                "Cannot normalize metrics key: {$e->getMessage()}",
                previous: $e
            );
        }
    }
}
