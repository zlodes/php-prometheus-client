<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Collector\ByType;

final class HistogramTimer
{
    private float $startedAt;

    public function __construct(
        private readonly HistogramCollector $collector,
    ) {
        $this->startedAt = microtime(true);
    }

    public function stop(): void
    {
        $elapsed = microtime(true) - $this->startedAt;

        $this->collector->update($elapsed);
    }
}
