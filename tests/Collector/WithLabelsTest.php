<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Collector;

use Zlodes\PrometheusClient\Collector\WithLabels;
use PHPUnit\Framework\TestCase;

class WithLabelsTest extends TestCase
{
    public function testReturnClone(): void
    {
        $somethingWithLabels = new class() {
            use WithLabels;

            public function getLabels(): array
            {
                return [];
            }
        };

        $clone = $somethingWithLabels->withLabels(['foo' => 'bar']);

        self::assertNotSame($somethingWithLabels, $clone);
    }
}
