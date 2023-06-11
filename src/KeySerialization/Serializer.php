<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\KeySerialization;

use Zlodes\PrometheusClient\Exceptions\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exceptions\MetricKeyUnserializationException;
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
