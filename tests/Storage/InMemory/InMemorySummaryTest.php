<?php

declare(strict_types=1);

namespace Storage\InMemory;

use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Storage\InMemory\InMemorySummary;

class InMemorySummaryTest extends TestCase
{
    public function testMaxValues(): void
    {
        $summary = new InMemorySummary(maxLength: 4);

        $summary->push(1, 2, 3, 4);
        self::assertSame([1,2,3,4], $summary->getItems());

        // Single push
        $summary->push(5);
        self::assertSame([2, 3, 4, 5], $summary->getItems());

        // Multiple push
        $summary->push(6, 7);
        self::assertSame([4, 5, 6, 7], $summary->getItems());
    }
}
