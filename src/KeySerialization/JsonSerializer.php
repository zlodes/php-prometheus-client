<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\KeySerialization;

use InvalidArgumentException;
use JsonException;
use Webmozart\Assert\Assert;
use Zlodes\PrometheusClient\Exceptions\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exceptions\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

final class JsonSerializer implements Serializer
{
    public function serialize(MetricNameWithLabels $metricNameWithLabels): string
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
            throw new MetricKeySerializationException("JSON encoding error", previous: $e);
        }

        return $name . '|' . $labelsString;
    }

    public function unserialize(string $key): MetricNameWithLabels
    {
        try {
            $nameWithLabelsRaw = explode('|', $key, 2);
            Assert::notEmpty($nameWithLabelsRaw);
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
            throw new MetricKeyUnserializationException(
                "Cannot unserialize metrics key: {$e->getMessage()}",
                previous: $e
            );
        }
    }
}
