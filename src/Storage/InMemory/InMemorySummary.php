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

    /**
     * @return list<int|float>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
