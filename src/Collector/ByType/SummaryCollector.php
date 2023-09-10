<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Collector\ByType;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusClient\Collector\Collector;
use Zlodes\PrometheusClient\Exception\StorageWriteException;
use Zlodes\PrometheusClient\Metric\Summary;
use Zlodes\PrometheusClient\StopWatch\HRTimeStopWatch;
use Zlodes\PrometheusClient\StopWatch\StopWatch;
use Zlodes\PrometheusClient\Storage\Commands\UpdateSummary;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

/**
 * @final
 */
final class SummaryCollector extends Collector
{
    /**
     * @internal Zlodes\PrometheusClient\Collector
     *
     * @param class-string<StopWatch> $stopWatchClass
     */
    public function __construct(
        private readonly Summary $summary,
        private readonly SummaryStorage $storage,
        private readonly LoggerInterface $logger,
        private readonly string $stopWatchClass = HRTimeStopWatch::class,
    ) {
    }

    public function update(float|int $value): void
    {
        $summary = $this->summary;
        $labels = $this->getLabels();

        try {
            $this->storage->updateSummary(
                new UpdateSummary(
                    new MetricNameWithLabels($summary->name, $labels),
                    $value,
                )
            );
        } catch (StorageWriteException $e) {
            $this->logger->error("Cannot persist Summary {$summary->name}: $e");
        }
    }

    public function startTimer(): StopWatch
    {
        $stopWatchClass = $this->stopWatchClass;

        return new $stopWatchClass(function (float $elapsed): void {
            $this->update($elapsed);
        });
    }
}
