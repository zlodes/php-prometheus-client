<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

use Zlodes\PrometheusExporter\Exceptions\MetricKeySerializationException;
use Zlodes\PrometheusExporter\Exceptions\MetricKeyUnserializationException;
use Zlodes\PrometheusExporter\Exceptions\StorageReadException;
use Zlodes\PrometheusExporter\Exceptions\StorageWriteException;
use Zlodes\PrometheusExporter\KeySerialization\JsonSerializer;
use Zlodes\PrometheusExporter\KeySerialization\Serializer;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;

final class InMemoryStorage implements Storage
{
    /** @var array<non-empty-string, float|int> */
    private array $storage = [];

    public function __construct(
        private readonly Serializer $metricKeySerializer = new JsonSerializer(),
    ) {
    }

    public function fetch(): array
    {
        $results = [];

        foreach ($this->storage as $serializedKey => $value) {
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

        return $results;
    }

    public function clear(): void
    {
        $this->storage = [];
    }

    public function setValue(MetricValue $value): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($value->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        $this->storage[$key] = $value->value;
    }

    public function incrementValue(MetricValue $value): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($value->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        if (!array_key_exists($key, $this->storage)) {
            $this->storage[$key] = $value->value;

            return;
        }

        $this->storage[$key] += $value->value;
    }
}
