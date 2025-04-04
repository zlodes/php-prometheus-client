<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Metric;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Zlodes\PrometheusClient\Metric\Summary;
use PHPUnit\Framework\TestCase;

final class SummaryTest extends TestCase
{
    public function testWithQuantiles(): void
    {
        $beforeQuantiles = new Summary('response_time', 'App response time');
        $afterQuantiles = $beforeQuantiles->withQuantiles([0.01, 0.5, 0.999]);

        self::assertNotSame($beforeQuantiles, $afterQuantiles);
        self::assertSame([0.01, 0.5, 0.999], $afterQuantiles->getQuantiles());
    }

    public function testWithMaxItems(): void
    {
        $beforeMaxItems = new Summary('response_time', 'App response time');
        $afterMaxItems = $beforeMaxItems->withMaxItems(1000);

        self::assertNotSame($beforeMaxItems, $afterMaxItems);
        self::assertSame(1000, $afterMaxItems->getMaxItems());
    }

    #[DataProvider('invalidMaxItemsDataProvider')]
    public function testWithMaxItemsWrongValues(int $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Summary('response_time', 'App response time'))
            ->withMaxItems($value);
    }

    public static function invalidMaxItemsDataProvider(): Generator
    {
        yield 'zero' => [
            0
        ];

        yield 'negative' => [
            -1
        ];
    }

    #[DataProvider('invalidQuantilesDataProvider')]
    public function testInvalidQuantiles(array $quantiles): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Summary('response_time', 'App response time'))
            ->withQuantiles($quantiles);
    }

    public static function invalidQuantilesDataProvider(): iterable
    {
        yield 'empty' => [
            []
        ];

        yield 'non-unique' => [
            [0.01, 0.01]
        ];
    }
}
