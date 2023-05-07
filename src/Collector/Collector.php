<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Collector;

interface Collector
{
    /**
     * @param non-empty-string $counterName
     * @param array<non-empty-string, non-empty-string> $labels
     * @param float|int $value
     *
     * @return void
     */
    public function counterIncrement(string $counterName, array $labels = [], float|int $value = 1): void;

    /**
     * @param non-empty-string $gaugeName
     * @param array<non-empty-string, non-empty-string> $labels
     * @param float|int $value
     *
     * @return void
     */
    public function gaugeIncrement(string $gaugeName, array $labels = [], float|int $value = 1): void;

    /**
     * @param non-empty-string $gaugeName
     * @param array<non-empty-string, non-empty-string> $labels
     * @param float|int $value
     *
     * @return void
     */
    public function gaugeSet(string $gaugeName, array $labels = [], float|int $value = 1): void;
}
