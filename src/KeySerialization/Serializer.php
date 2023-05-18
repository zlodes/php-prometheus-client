<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\KeySerialization;

use Zlodes\PrometheusExporter\Exceptions\MetricKeySerializationException;
use Zlodes\PrometheusExporter\Exceptions\MetricKeyUnserializationException;
use Zlodes\PrometheusExporter\Storage\DTO\MetricNameWithLabels;

interface Serializer
{
    /**
     * @return non-empty-string
     *
     * @throws MetricKeySerializationException
     */
    public function serialize(MetricNameWithLabels $metricNameWithLabels): string;

    /**
     * @param non-empty-string $key
     *
     * @return MetricNameWithLabels
     *
     * @throws MetricKeyUnserializationException
     */
    public function unserialize(string $key): MetricNameWithLabels;
}
