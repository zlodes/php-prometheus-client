<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Summary;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Summary\QuantileCalculator;

class QuantileCalculatorTest extends TestCase
{
    #[DataProvider('quantilesDataProvider')]
    public function testCalculate(array $items, float $actualResult, float $expectedResult): void
    {
        $actualResult = QuantileCalculator::calculate($items, $actualResult);

        self::assertEquals($expectedResult, $actualResult);
    }

    public function testCalculateEmptyItems(): void
    {
        $this->expectException(InvalidArgumentException::class);

        QuantileCalculator::calculate([], 0.5);
    }

    public static function quantilesDataProvider(): iterable
    {
        // [
        //   items array,
        //   quantile,
        //   expected value
        // ]

        yield 'one value 0.01' => [
            [42],
            0.01,
            42,
        ];

        yield 'one value 0.5' => [
            [42],
            0.5,
            42,
        ];

        yield 'one value 0.99' => [
            [42],
            0.99,
            42,
        ];

        yield 'one value 1.00' => [
            [42],
            1,
            42,
        ];

        yield 'two values 0.01' => [
            [100, 300],
            0.01,
            102,
        ];

        yield 'two values 0.5' => [
            [100, 300],
            0.5,
            200,
        ];

        yield 'two values 0.99' => [
            [100, 300],
            0.99,
            298,
        ];

        yield 'two values 1.00' => [
            [100, 300],
            1,
            300,
        ];

        yield 'many values 0.00001' => [
            [100, 200, 300, 400, 500, 600, 700, 800, 900],
            0.00001,
            100.008,
        ];

        yield 'many values 0.5' => [
            [600, 700, 800, 900, 100, 200, 300, 400, 500],
            0.5,
            500,
        ];

        yield 'many values 0.99' => [
            [100, 200, 300, 400, 500, 600, 700, 800, 900],
            0.99,
            892,
        ];

        yield 'many values 1.00' => [
            [900, 100, 200, 300, 400, 500, 600, 700, 800],
            1,
            900,
        ];

        yield 'many values 0.701' => [
            [100, 200, 300, 400, 500, 600, 700, 800, 900],
            0.701,
            660.8,
        ];
    }

}
