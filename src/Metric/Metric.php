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
        private readonly string $name,
        private readonly string $help,
    ) {
        Assert::regex($name, '/^[a-zA-Z][a-zA-Z0-9_:]*$/', 'Metric name MUST be in snake case');
    }

    /**
     * @return string Will be shown in metric output
     */
    abstract public function getPrometheusType(): string;

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
     * @return list<string> Names of metrics that should be output beside this metric
     */
    public function getDependentMetrics(): array
    {
        return [];
    }
}
