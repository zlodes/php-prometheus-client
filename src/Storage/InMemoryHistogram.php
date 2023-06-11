<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage;

final class InMemoryHistogram
{
    private const INFINITY_KEY = "+Inf";

    /** @var non-empty-array<non-empty-string, int|float> */
    private array $buckets;
    private int $count = 0;
    private float $sum = 0;

    /**
     * @param non-empty-list<float> $bucketThresholds
     */
    public function __construct(private readonly array $bucketThresholds)
    {
        $buckets = [];

        foreach ($this->bucketThresholds as $threshold) {
            /** @phpstan-var non-empty-string $key */
            $key = (string) $threshold;

            $buckets[$key] = 0.0;
        }

        $buckets[self::INFINITY_KEY] = 0.0;

        $this->buckets = $buckets;
    }

    public function registerValue(float|int $value): void
    {
        $this->count++;
        $this->sum += $value;

        foreach ($this->bucketThresholds as $bucket) {
            if ($value <= $bucket) {
                /** @phpstan-var non-empty-string $key */
                $key = (string) $bucket;

                $this->buckets[$key]++;
            }
        }

        $this->buckets[self::INFINITY_KEY]++;
    }

    /**
     * @return non-empty-array<non-empty-string|positive-int, int|float>
     */
    public function getBuckets(): array
    {
        return $this->buckets;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getSum(): float
    {
        return $this->sum;
    }
}
