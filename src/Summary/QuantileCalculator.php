<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Summary;

use Webmozart\Assert\Assert;

final class QuantileCalculator
{
    /**
     * @param non-empty-list<int|float> $items
     * @param float $quantile
     *
     * @return int|float Calculated quantile
     */
    public static function calculate(array $items, float $quantile): int|float
    {
        Assert::notEmpty($items);
        Assert::range($quantile, 0.0, 1.0);

        $itemsCount = count($items);

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
