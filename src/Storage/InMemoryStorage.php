<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

use Zlodes\PrometheusExporter\Exceptions\MetricKeySerializationException;
use Zlodes\PrometheusExporter\Exceptions\MetricKeyUnserializationException;
use Zlodes\PrometheusExporter\Exceptions\StorageReadException;
use Zlodes\PrometheusExporter\Exceptions\StorageWriteException;
use Zlodes\PrometheusExporter\KeySerialization\JsonSerializer;
use Zlodes\PrometheusExporter\KeySerialization\Serializer;
use Zlodes\PrometheusExporter\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;

final class InMemoryStorage implements Storage
{
    /** @var array<non-empty-string, float|int> */
    private array $simpleMetricsStorage = [];

    /** @var array<non-empty-string, InMemoryHistogram> */
    private array $histogramStorage = [];

    public function __construct(
        private readonly Serializer $metricKeySerializer = new JsonSerializer(),
    ) {
    }

    public function fetch(): array
    {
        $results = [];

        foreach ($this->simpleMetricsStorage as $serializedKey => $value) {
            try {
                $results[] = new MetricValue(
                    $this->metricKeySerializer->unserialize($serializedKey),
                    $value
                );
            } catch (MetricKeyUnserializationException $e) {
                throw new StorageReadException(
                    "Fetch error. Cannot unserialize metrics key for key: $serializedKey",
                    previous: $e
                );
            }
        }

        foreach ($this->histogramStorage as $serializedKey => $histogram) {
            try {
                $keyWithLabels = $this->metricKeySerializer->unserialize($serializedKey);
            } catch (MetricKeyUnserializationException $e) {
                throw new StorageReadException(
                    "Fetch error. Cannot unserialize metrics key for key: $serializedKey",
                    previous: $e
                );
            }

            foreach ($histogram->getQuantiles() as $quantile => $value) {
                $results[] = new MetricValue(
                    new MetricNameWithLabels(
                        $keyWithLabels->metricName,
                        [
                            ...$keyWithLabels->labels,
                            'le' => (string) $quantile,
                        ]
                    ),
                    $value
                );
            }

            $results[] = new MetricValue(
                new MetricNameWithLabels(
                    $keyWithLabels->metricName . '_sum',
                    $keyWithLabels->labels
                ),
                $histogram->getSum()
            );

            $results[] = new MetricValue(
                new MetricNameWithLabels(
                    $keyWithLabels->metricName . '_count',
                    $keyWithLabels->labels
                ),
                $histogram->getCount()
            );
        }

        return $results;
    }

    public function clear(): void
    {
        $this->simpleMetricsStorage = [];
    }

    public function setValue(MetricValue $value): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($value->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        $this->simpleMetricsStorage[$key] = $value->value;
    }

    public function incrementValue(MetricValue $value): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($value->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        if (!array_key_exists($key, $this->simpleMetricsStorage)) {
            $this->simpleMetricsStorage[$key] = $value->value;

            return;
        }

        $this->simpleMetricsStorage[$key] += $value->value;
    }

    /**
     * @param MetricValue $value
     * @param non-empty-list<float> $buckets
     *
     * @return void
     */
    public function persistHistogram(MetricValue $value, array $buckets): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($value->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        $histogram = $this->histogramStorage[$key] ??= new InMemoryHistogram($buckets);

        $histogram->registerValue($value->value);
    }
}
