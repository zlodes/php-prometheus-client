<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Storage\InMemory;

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
        Assert::greaterThan($maxLength, 2);
        Assert::maxCount($items, $maxLength);
    }

    /**
     * @no-named-arguments
     */
    public function push(int|float ...$values): void
    {
        array_push($this->items, ...$values);

        $newCount = count($this->items);
        $countDifference = $newCount - $this->maxLength;

        if ($countDifference > 0) {
            $this->items = array_slice($this->items, $countDifference);
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

    /**
     * @return list<int|float>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param float $quantile
     *
     * @return float|int|null Calculated quantile value or null if there are no items
     */
    public function getQuantile(float $quantile): float|int|null
    {
        Assert::range($quantile, 0.0, 1.0);

        $items = $this->items;

        $itemsCount = count($items);

        if ($itemsCount === 0) {
            return null;
        }

        if ($itemsCount === 1) {
            return $items[0];
        }

        sort($items);

        $index = $quantile * (count($items) - 1);

        $integerIndex = (int) $index;
        $fractionalPartOfIndex = fmod($index, 1);

        $integerIndexValue = $items[$integerIndex];

        // If index is a whole number we should return the exact item
        if ($fractionalPartOfIndex === 0.0) {
            return $integerIndexValue;
        }

        $nextIndexValue = $items[$integerIndex + 1];

        // Linear interpolation to get the exact quantile value
        return $integerIndexValue + $fractionalPartOfIndex * ($nextIndexValue - $integerIndexValue);
    }
}
