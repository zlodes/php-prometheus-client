<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Fetcher\DTO;

use Zlodes\PrometheusClient\Metric\Metric;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;

final readonly class FetchedMetric
{
    /**
     * @param list<MetricValue> $values
     */
    public function __construct(
        public Metric $metric,
        public array $values,
    ) {
    }
}
