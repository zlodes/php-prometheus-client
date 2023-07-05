<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage;

use Generator;
use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\JsonSerializer;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

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

    public function fetch(): Generator
    {
        yield from $this->fetchGaugeAndCounterMetrics();

        yield from $this->fetchHistogramMetrics();
    }

    public function clear(): void
    {
        $this->simpleMetricsStorage = [];
        $this->histogramStorage = [];
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

    /**
     * @return Generator<int, MetricValue>
     */
    private function fetchGaugeAndCounterMetrics(): Generator
    {
        foreach ($this->simpleMetricsStorage as $serializedKey => $value) {
            try {
                yield new MetricValue(
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
    }

    /**
     * @return Generator<int, MetricValue>
     */
    private function fetchHistogramMetrics(): Generator
    {
        foreach ($this->histogramStorage as $serializedKey => $histogram) {
            try {
                $keyWithLabels = $this->metricKeySerializer->unserialize($serializedKey);
            } catch (MetricKeyUnserializationException $e) {
                throw new StorageReadException(
                    "Fetch error. Cannot unserialize metrics key for key: $serializedKey",
                    previous: $e
                );
            }

            foreach ($histogram->getBuckets() as $bucket => $value) {
                yield new MetricValue(
                    new MetricNameWithLabels(
                        $keyWithLabels->metricName,
                        [
                            ...$keyWithLabels->labels,
                            'le' => (string) $bucket,
                        ]
                    ),
                    $value
                );
            }

            yield new MetricValue(
                new MetricNameWithLabels(
                    $keyWithLabels->metricName . '_sum',
                    $keyWithLabels->labels
                ),
                $histogram->getSum()
            );

            yield new MetricValue(
                new MetricNameWithLabels(
                    $keyWithLabels->metricName . '_count',
                    $keyWithLabels->labels
                ),
                $histogram->getCount()
            );
        }
    }
}
