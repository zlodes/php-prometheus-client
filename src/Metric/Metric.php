<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Metric;

interface Metric
{
    /**
     * @return string Will be shown near metric output
     */
    public function getPrometheusType(): string;

    /**
     * @return non-empty-string Metric name
     */
    public function getName(): string;

    /**
     * @return non-empty-string Will be shown near metric output
     */
    public function getHelp(): string;

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getInitialLabels(): array;

    /**
     * @return list<non-empty-string> List of dependent metrics, e.g. metrics with _count and _sum suffix for Histogram
     */
    public function getDependentMetrics(): array;
}
