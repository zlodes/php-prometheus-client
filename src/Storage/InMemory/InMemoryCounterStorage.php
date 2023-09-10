<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\InMemory;

use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\JsonSerializer;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Storage\Commands\IncrementCounter;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

final class InMemoryCounterStorage implements CounterStorage
{
    /** @var array<non-empty-string, float|int> */
    private array $storage = [];

    public function __construct(
        private readonly Serializer $metricKeySerializer = new JsonSerializer(),
    ) {
    }

    public function incrementCounter(IncrementCounter $command): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($command->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        if (!array_key_exists($key, $this->storage)) {
            $this->storage[$key] = $command->value;

            return;
        }

        $this->storage[$key] += $command->value;
    }

    public function fetchCounters(): iterable
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

    public function clearCounters(): void
    {
        $this->storage = [];
    }
}
