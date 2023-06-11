<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\KeySerialization;

use Zlodes\PrometheusClient\Exception\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exception\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

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
