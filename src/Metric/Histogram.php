<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Metric;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Histogram extends BaseMetric
{
    /** @var non-empty-list<float> */
    private array $buckets = [
        0.005,
        0.01,
        0.025,
        0.05,
        0.075,
        0.1,
        0.25,
        0.5,
        0.75,
        1,
        2.5,
        5,
        7.5,
        10,
    ];

    public function getPrometheusType(): string
    {
        return 'histogram';
    }

    public function getDependentMetrics(): array
    {
        $selfName = $this->getName();

        return [
            "{$selfName}_sum",
            "{$selfName}_count",
        ];
    }

    /**
     * @return non-empty-list<float>
     */
    public function getBuckets(): array
    {
        return $this->buckets;
    }

    /**
     * @param non-empty-list<float> $buckets
     *
     * @return self
     */
    public function withBuckets(array $buckets): self
    {
        $this->validateBuckets($buckets);

        $histogram = clone $this;
        $histogram->buckets = $buckets;

        return $histogram;
    }

    /**
     * @param non-empty-list<float> $buckets
     *
     * @throws InvalidArgumentException
     */
    private function validateBuckets(array $buckets): void
    {
        Assert::notEmpty($buckets);
        Assert::allNumeric($buckets);
        Assert::uniqueValues($buckets);
        Assert::allGreaterThanEq($buckets, 0);

        $bucketsSize = count($buckets);

        for ($i = 1; $i < $bucketsSize; $i++) {
            Assert::greaterThan($buckets[$i], $buckets[$i - 1], 'Buckets MUST be sorted in increasing order');
        }
    }
}
