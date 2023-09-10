<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\InMemory;

use Webmozart\Assert\Assert;
use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Exception\StorageReadException;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\KeySerialization\JsonSerializer;
use Zlodes\PrometheusClient\KeySerialization\Serializer;
use Zlodes\PrometheusClient\Storage\Commands\UpdateSummary;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;
use Zlodes\PrometheusClient\Storage\DTO\SummaryMetricValue;

final class InMemorySummaryStorage implements SummaryStorage
{
    /** @var array<non-empty-string, InMemorySummary> */
    private array $storage = [];

    public function __construct(
        private readonly Serializer $metricKeySerializer = new JsonSerializer(),
    ) {
    }

    public function updateSummary(UpdateSummary $command): void
    {
        try {
            $key = $this->metricKeySerializer->serialize($command->metricNameWithLabels);
        } catch (MetricKeySerializationException $e) {
            throw new StorageWriteException('Cannot serialize metric key', previous: $e);
        }

        $summary = $this->storage[$key] ??= new InMemorySummary();

        $summary->push($command->value);
    }

    public function fetchSummaries(): iterable
    {
        foreach ($this->storage as $serializedKey => $summary) {
            try {
                $keyWithLabels = $this->metricKeySerializer->unserialize($serializedKey);
            } catch (MetricKeyUnserializationException $e) {
                throw new StorageReadException(
                    "Fetch error. Cannot unserialize metrics key for key: $serializedKey",
                    previous: $e
                );
            }

            $values = $summary->getItems();
            Assert::notEmpty($values);

            yield new SummaryMetricValue(
                $keyWithLabels,
                $values,
            );
        }
    }

    public function clearSummaries(): void
    {
        $this->storage = [];
    }
}
