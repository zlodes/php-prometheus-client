<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Collector;

/**
 * @psalm-internal Zlodes\PrometheusExporter\Collector
 */
trait WithLabels
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $labels = [];

    /**
     * @param non-empty-array<non-empty-string, non-empty-string> $labels
     *
     * @return self
     */
    public function withLabels(array $labels): self
    {
        $instance = clone $this;
        $instance->labels = $labels;

        return $instance;
    }
}
