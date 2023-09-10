<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Fetcher;

use Zlodes\PrometheusClient\Fetcher\DTO\FetchedMetric;

/**
 * Fetcher gets metrics from storage, matches them with their definition in Registry
 * and provide handy DTO describing each metric with their values
 */
interface Fetcher
{
    /**
     * @return iterable<int, FetchedMetric> Each registered metric within its own values
     */
    public function fetch(): iterable;
}
