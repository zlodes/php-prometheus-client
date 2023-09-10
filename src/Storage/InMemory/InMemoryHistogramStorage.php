<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\InMemory;

use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\JsonSerializer;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Storage\Commands\UpdateHistogram;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;
use Zlodes\PrometheusClient\Storage\DTO\HistogramMetricValue;

final class InMemoryHistogramStorage implements HistogramStorage
{
    /** @var array<non-empty-string, InMemoryHistogram> */
    private array $storage = [];

    public function __construct(
        private readonly Serializer $metricKeySerializer = new JsonSerializer(),
    ) {
    }

    public function updateHistogram(UpdateHistogram $command): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($command->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        $histogram = $this->storage[$key] ??= new InMemoryHistogram($command->buckets);

        $histogram->registerValue($command->value);
    }

    public function fetchHistograms(): iterable
    {
        foreach ($this->storage as $serializedKey => $histogram) {
            try {
                $keyWithLabels = $this->metricKeySerializer->unserialize($serializedKey);
            } catch (MetricKeyUnserializationException $e) {
                throw new StorageReadException(
                    "Fetch error. Cannot unserialize metrics key for key: $serializedKey",
                    previous: $e
                );
            }

            yield new HistogramMetricValue(
                $keyWithLabels,
                $histogram->getBuckets(),
                $histogram->getSum(),
                $histogram->getCount(),
            );
        }
    }

    public function clearHistograms(): void
    {
        $this->storage = [];
    }
}
