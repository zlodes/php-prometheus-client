<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Storage\DTO;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use PHPUnit\Framework\TestCase;

class MetricNameWithLabelsTest extends TestCase
{
    #[DataProvider('wrongLabelsDataProvider')]
    public function testWrongLabels(array $labels): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MetricNameWithLabels('foo', $labels);
    }

    public static function wrongLabelsDataProvider(): iterable
    {
        yield 'numbers list' => [[1, 2, 3]];
        yield 'strings list' => [['foo', 'bar']];
        yield 'empty string' => [['']];
        yield 'one empty string' => [['foo' => 'bar', 'baz' => '']];
    }
}
