<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\InMemory;

use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\JsonSerializer;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Storage\Commands\UpdateGauge;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

final class InMemoryGaugeStorage implements GaugeStorage
{
    /** @var array<non-empty-string, float|int> */
    private array $storage = [];

    public function __construct(
        private readonly Serializer $metricKeySerializer = new JsonSerializer(),
    ) {
    }

    public function updateGauge(UpdateGauge $command): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($command->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        $this->storage[$key] = $command->value;
    }

    public function fetchGauges(): iterable
    {
        foreach ($this->storage as $serializedKey => $value) {
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

    public function clearGauges(): void
    {
        $this->storage = [];
    }
}
