<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

use Zlodes\PrometheusExporter\DTO\MetricValue;
use Zlodes\PrometheusExporter\Exceptions\StorageReadException;
use Zlodes\PrometheusExporter\Normalization\Contracts\MetricKeyDenormalizer;
use Zlodes\PrometheusExporter\Normalization\Contracts\MetricKeyNormalizer;
use Zlodes\PrometheusExporter\Normalization\Exceptions\CannotNormalizeMetricsKey;
use Zlodes\PrometheusExporter\Normalization\JsonMetricKeyDenormalizer;
use Zlodes\PrometheusExporter\Normalization\JsonMetricKeyNormalizer;

final class InMemoryStorage implements Storage
{
    /** @var array<non-empty-string, float|int> */
    private array $storage = [];

    public function __construct(
        private readonly MetricKeyNormalizer $metricKeyNormalizer = new JsonMetricKeyNormalizer(),
        private readonly MetricKeyDenormalizer $metricKeyDenormalizer = new JsonMetricKeyDenormalizer(),
    ) {
    }

    public function fetch(): array
    {
        $results = [];

        foreach ($this->storage as $denormalizedKey => $value) {
            try {
                $results[] = new MetricValue(
                    $this->metricKeyNormalizer->normalize($denormalizedKey),
                    $value
                );
            } catch (CannotNormalizeMetricsKey $e) {
                throw new StorageReadException(
                    "Fetch error. Cannot normalize metrics key for key: $denormalizedKey",
                    previous: $e
                );
            }
        }

        return $results;
    }

    public function flush(): void
    {
        $this->storage = [];
    }

    public function setValue(MetricValue $value): void
    {
        $key = $this->metricKeyDenormalizer->denormalize($value->metricNameWithLabels);

        $this->storage[$key] = $value->value;
    }

    public function incrementValue(MetricValue $value): void
    {
        $key = $this->metricKeyDenormalizer->denormalize($value->metricNameWithLabels);

        if (!array_key_exists($key, $this->storage)) {
            $this->storage[$key] = $value->value;

            return;
        }

        $this->storage[$key] += $value->value;
    }
}
