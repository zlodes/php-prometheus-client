<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

final class InMemoryHistogram
{
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
            $buckets[(string) $threshold] = 0.0;
        }

        $buckets["+Inf"] = 0.0;

        $this->buckets = $buckets;
    }

    public function registerValue(float|int $value): void
    {
        $this->count++;
        $this->sum += $value;

        foreach ($this->bucketThresholds as $bucket) {
            if ($value <= $bucket) {
                $key = (string) $bucket;

                $this->buckets[$key]++;
            }
        }

        $this->buckets["+Inf"]++;
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
