<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\MetricTypes;

use Webmozart\Assert\Assert;

/**
 * TODO: Might be refactored
 *
 * This class shouldn't be used directly anywhere.
 *
 * @internal Zlodes\PrometheusExporter\MetricTypes
 */
abstract class BaseMetric implements Metric
{
    /**
     * @param non-empty-string $name Metric name
     * @param non-empty-string $help Metric help (will be shown near metric output)
     * @param array<non-empty-string, non-empty-string> $initialLabels Labels which will be always presented
     */
    final public function __construct(
        private readonly string $name,
        private readonly string $help,
        private readonly array $initialLabels = [],
    ) {
        Assert::regex($name, '/^[a-zA-Z_:][a-zA-Z0-9_:]*$/', 'Metric name MUST be in snake case');
        Assert::allString($initialLabels);
        Assert::allNotEmpty($initialLabels);
    }

    /**
     * @return non-empty-string Metric name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string Will be shown near metric output
     */
    public function getHelp(): string
    {
        return $this->help;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getInitialLabels(): array
    {
        return $this->initialLabels;
    }

    public function getDependentMetrics(): array
    {
        return [];
    }
}
