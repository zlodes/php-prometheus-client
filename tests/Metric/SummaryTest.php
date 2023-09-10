<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Metric;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Zlodes\PrometheusClient\Metric\Summary;
use PHPUnit\Framework\TestCase;

class SummaryTest extends TestCase
{
    public function testWithQuantiles(): void
    {
        $beforeQuantiles = new Summary('response_time', 'App response time');
        $afterQuantiles = $beforeQuantiles->withQuantiles([0.01, 0.5, 0.999]);

        self::assertNotSame($beforeQuantiles, $afterQuantiles);
        self::assertSame([0.01, 0.5, 0.999], $afterQuantiles->getQuantiles());
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
