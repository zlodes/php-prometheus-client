<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;
use Zlodes\PrometheusClient\Collector\Collector;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Storage\Commands\IncrementCounter;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

/**
 * @final
 */
class CounterCollector extends Collector
{
    /**
     * @internal Zlodes\PrometheusClient\Collector
     */
    public function __construct(
        private readonly Counter $counter,
        private readonly CounterStorage $storage,
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
        $labels = $this->getLabels();

        try {
            $this->storage->incrementCounter(
                new IncrementCounter(
                    new MetricNameWithLabels($counter->name, $labels),
                    $value
                )
            );
        } catch (StorageWriteException $e) {
            $this->logger->error("Cannot increment counter {$counter->name}: $e");
        }
    }
}
