<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage;

use Webmozart\Assert\Assert;

final class InMemorySummary
{
    /**
     * @param list<float|int> $items
     */
    public function __construct(
        private array $items = [],
        private readonly int $maxLength = 1_000_000,
    ) {
    }

    /**
     * @no-named-arguments
     */
    public function push(int|float ...$values): void
    {
        array_push($this->items, ...$values);

        if (count($this->items) > $this->maxLength) {
            array_shift($this->items);
        }
    }

    public function getCount(): int
    {
        return count($this->items);
    }

    public function getSum(): float
    {
        return array_sum($this->items);
    }

    public function getQuantile(float $quantile): float|int
    {
        Assert::range($quantile, 0.0, 1.0);

        $items = $this->items;

        $itemsCount = count($items);

        // TODO: Is that correct?
        if ($itemsCount === 0) {
            return 0;
        }

        if ($itemsCount === 1) {
            return $items[0];
        }

        sort($items);

        $index = $quantile * (count($items) - 1);

        // If index is a whole number we should return the exact item
        if (fmod($index, 1) === 0.0) {
            return $items[(int) $index];
        }

        // Otherwise we should interpolate between the two nearest items
        $indexBefore = (int) floor($index);
        $indexAfter = (int) ceil($index);

        // Index after might be out of bounds
        if ($indexAfter >= $itemsCount - 1) {
            return $items[$indexBefore];
        }

        $valueBefore = $items[$indexBefore];
        $valueAfter = $items[$indexAfter];

        // Linear interpolation to get the exact quantile value
        return $valueBefore + ($valueAfter - $valueBefore) * ($index - $indexBefore);
    }
}
