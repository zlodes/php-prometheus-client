<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Collector;

use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Collector\Collector;

class CollectorTest extends TestCase
{
    public function testReturnClone(): void
    {
        $somethingWithLabels = new class() extends Collector{
            public function getLabels(): array
            {
                return [];
            }
        };

        $clone = $somethingWithLabels->withLabels(['foo' => 'bar']);

        self::assertNotSame($somethingWithLabels, $clone);
    }
}
