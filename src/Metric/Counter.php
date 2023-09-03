<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Metric;

final class Counter extends Metric
{
    public function getPrometheusType(): string
    {
        return 'counter';
    }
}
