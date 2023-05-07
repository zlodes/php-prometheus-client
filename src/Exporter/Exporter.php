<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Exporter;

interface Exporter
{
    /**
     * String example:
     *
     * HELP <metric name> Some help. Initial value: <initial value>
     * TYPE <metric name> <metric type>
     * <metric name>{<label name>=<label value>,<label name>=<label value>} <metric value>
     * <metric name>{<label name>=<label value>,<label name>=<label value>} <metric value>
     *
     * @return iterable<int, string>
     */
    public function export(): iterable;
}
