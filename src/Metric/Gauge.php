<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Metric;

final class Gauge extends Metric
{
    public function getPrometheusType(): string
    {
        return 'gauge';
    }
}
