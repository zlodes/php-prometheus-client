<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Exporter;

interface Exporter
{
    /**
     * @return iterable<int, string>
     */
    public function export(): iterable;
}
