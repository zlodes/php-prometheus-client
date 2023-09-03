<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector;

abstract class Collector
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $labels = [];

    /**
     * @param non-empty-array<non-empty-string, non-empty-string> $labels
     *
     * @return static
     */
    public function withLabels(array $labels): static
    {
        $instance = clone $this;
        $instance->labels = $labels;

        return $instance;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getLabels(): array
    {
        return $this->labels;
    }
}
