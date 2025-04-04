<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Metric;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class Summary extends Metric
{
    /** @var non-empty-list<float> */
    private array $quantiles = [0.01, 0.05, 0.5, 0.9, 0.95, 0.99, 0.999];

    /** @var positive-int */
    private int $maxItems = 1_000_000;

    public function getPrometheusType(): string
    {
        return 'summary';
    }

    /**
     * @return non-empty-list<float>
     */
    public function getQuantiles(): array
    {
        return $this->quantiles;
    }

    /**
     * @param non-empty-list<float> $quantiles
     *
     * @return $this
     */
    public function withQuantiles(array $quantiles): self
    {
        $this->validateQuantiles($quantiles);

        $summary = clone $this;
        $summary->quantiles = $quantiles;

        return $summary;
    }

    /**
     * @return positive-int
     */
    public function getMaxItems(): int
    {
        return $this->maxItems;
    }

    public function withMaxItems(int $maxItems): self
    {
        Assert::positiveInteger($maxItems);

        $summary = clone $this;
        $summary->maxItems = $maxItems;

        return $summary;
    }

    /**
     * @param list<mixed> $quantiles
     *
     * @throws InvalidArgumentException
     */
    private function validateQuantiles(array $quantiles): void
    {
        Assert::notEmpty($quantiles);
        Assert::allNumeric($quantiles);
        Assert::uniqueValues($quantiles);

        foreach ($quantiles as $quantile) {
            Assert::range($quantile, 0.0, 1.0, 'Quantile MUST be in range [0.0, 1.0]');
        }
    }
}
