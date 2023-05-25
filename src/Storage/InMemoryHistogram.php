<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

final class InMemoryHistogram
{
    /** @var non-empty-array<non-empty-string, int|float> */
    private array $quantiles;
    private int $count = 0;
    private float $sum = 0;

    /**
     * @param non-empty-list<float> $buckets
     */
    public function __construct(
        private readonly array $buckets,
    ) {
        $quantiles = [];

        foreach ($this->buckets as $bucket) {
            $quantiles[(string) $bucket] = 0.0;
        }

        $this->quantiles = $quantiles;
    }

    public function registerValue(float|int $value): void
    {
        $this->count++;
        $this->sum += $value;

        foreach ($this->buckets as $bucket) {
            if ($value <= $bucket) {
                $key = (string) $bucket;

                $this->quantiles[$key]++;
            }
        }

        $this->quantiles["+Inf"]++;
    }

    /**
     * @return non-empty-array<non-empty-string|positive-int, int|float>
     */
    public function getQuantiles(): array
    {
        return $this->quantiles;
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
