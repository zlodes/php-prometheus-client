<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Metric;

use Webmozart\Assert\Assert;

/**
 * This class shouldn't be used directly anywhere.
 */
abstract class Metric
{
    /**
     * @param non-empty-string $name Metric name
     * @param non-empty-string $help Metric help (will be shown near metric output)
     */
    final public function __construct(
        public readonly string $name,
        public readonly string $help,
    ) {
        Assert::regex($name, '/^[a-zA-Z][a-zA-Z0-9_:]*$/', 'Metric name MUST be in snake case');
    }

    /**
     * @return string Will be shown in metric output
     */
    abstract public function getPrometheusType(): string;
}
