<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\MetricTypes;

/**
 * @see https://prometheus.io/docs/concepts/metric_types/
 */
enum MetricType: string
{
    case COUNTER = 'counter';
    case GAUGE = 'gauge';
    case HISTOGRAM = 'histogram';
    case SUMMARY = 'summary';
}
