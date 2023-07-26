<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;
use Zlodes\PrometheusClient\Collector\WithLabels;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\Storage;

/**
 * @internal Zlodes\PrometheusClient\Collector
 * @final
 */
class CounterCollector
{
    use WithLabels;

    public function __construct(
        private readonly Counter $counter,
        private readonly Storage $storage,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param positive-int|float $value
     *
     * @return void
     */
    public function increment(int|float $value = 1): void
    {
        Assert::true($value > 0, 'Increment value of Counter metric MUST be positive');

        $counter = $this->counter;
        $labels = $this->composeLabels();

        try {
            $this->storage->incrementValue(
                new MetricValue(
                    new MetricNameWithLabels($counter->getName(), $labels),
                    $value
                )
            );
        } catch (StorageWriteException $e) {
            $this->logger->error("Cannot increment counter {$counter->getName()}: $e");
        }
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    private function composeLabels(): array
    {
        return array_merge($this->counter->getInitialLabels(), $this->labels);
    }
}
