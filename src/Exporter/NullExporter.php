<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Exporter;

final class NullExporter implements Exporter
{
    public function export(): iterable
    {
        return [];
    }
}
