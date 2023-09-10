<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Fetcher;

use Generator;
use Zlodes\PrometheusClient\Fetcher\DTO\FetchedMetric;
use Zlodes\PrometheusClient\Metric\Counter;
use Zlodes\PrometheusClient\Metric\Gauge;
use Zlodes\PrometheusClient\Metric\Histogram;
use Zlodes\PrometheusClient\Metric\Summary;
use Zlodes\PrometheusClient\Registry\Registry;
use Zlodes\PrometheusClient\Storage\Contracts\CounterStorage;
use Zlodes\PrometheusClient\Storage\Contracts\GaugeStorage;
use Zlodes\PrometheusClient\Storage\Contracts\HistogramStorage;
use Zlodes\PrometheusClient\Storage\Contracts\SummaryStorage;
use Zlodes\PrometheusClient\Storage\DTO\HistogramMetricValue;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;
use Zlodes\PrometheusClient\Storage\DTO\MetricValue;
use Zlodes\PrometheusClient\Storage\DTO\SummaryMetricValue;
use Zlodes\PrometheusClient\Summary\QuantileCalculator;

final class StoredMetricsFetcher implements Fetcher
{
    public function __construct(
        private readonly Registry $registry,
        private readonly CounterStorage $counterStorage,
        private readonly GaugeStorage $gaugeStorage,
        private readonly HistogramStorage $histogramStorage,
        private readonly SummaryStorage $summaryStorage,
    ) {
    }

    public function fetch(): iterable
    {
        yield from $this->fetchCounters();

        yield from $this->fetchGauges();

        yield from $this->fetchHistograms();

        yield from $this->fetchSummaries();
    }

    /**
     * @return Generator<int, FetchedMetric>
     */
    private function fetchCounters(): Generator
    {
        /** @var array<non-empty-string, list<MetricValue>> $countersByName */
        $countersByName = [];

        foreach ($this->counterStorage->fetchCounters() as $counterValue) {
            $nameWithLabels = $counterValue->metricNameWithLabels;

            $countersByName[$nameWithLabels->metricName][] = $counterValue;
        }

        foreach ($countersByName as $metricName => $values) {
            $metric = $this->registry->getMetric($metricName, Counter::class);

            yield new FetchedMetric(
                $metric,
                $values
            );
        }
    }

    /**
     * @return Generator<int, FetchedMetric>
     */
    private function fetchGauges(): Generator
    {
        /** @var array<non-empty-string, list<MetricValue>> $gaugesByName */
        $gaugesByName = [];

        foreach ($this->gaugeStorage->fetchGauges() as $counterValue) {
            $nameWithLabels = $counterValue->metricNameWithLabels;

            $gaugesByName[$nameWithLabels->metricName][] = $counterValue;
        }

        foreach ($gaugesByName as $metricName => $values) {
            $metric = $this->registry->getMetric($metricName, Gauge::class);

            yield new FetchedMetric(
                $metric,
                $values
            );
        }
    }

    /**
     * @return Generator<int, FetchedMetric>
     */
    private function fetchHistograms(): Generator
    {
        /** @var array<non-empty-string, list<HistogramMetricValue>> $histogramsByName */
        $histogramsByName = [];

        foreach ($this->histogramStorage->fetchHistograms() as $histograms) {
            $nameWithLabels = $histograms->metricNameWithLabels;

            $histogramsByName[$nameWithLabels->metricName][] = $histograms;
        }

        foreach ($histogramsByName as $metricName => $histograms) {
            $metric = $this->registry->getMetric($metricName, Histogram::class);

            $values = [];

            foreach ($histograms as $histogram) {
                // Add all the buckets
                foreach ($histogram->buckets as $bucketName => $bucketValue) {
                    $values[] = new MetricValue(
                        new MetricNameWithLabels(
                            $metricName,
                            [
                                ...$histogram->metricNameWithLabels->labels,
                                'le' => (string) $bucketName,
                            ]
                        ),
                        $bucketValue
                    );
                }

                // Add sum
                $values[] = new MetricValue(
                    new MetricNameWithLabels(
                        "{$metricName}_sum",
                        $histogram->metricNameWithLabels->labels
                    ),
                    $histogram->sum
                );

                // Add count
                $values[] = new MetricValue(
                    new MetricNameWithLabels(
                        "{$metricName}_count",
                        $histogram->metricNameWithLabels->labels
                    ),
                    $histogram->count
                );
            }

            yield new FetchedMetric(
                $metric,
                $values
            );
        }
    }

    /**
     * @return Generator<int, FetchedMetric>
     */
    private function fetchSummaries(): Generator
    {
        /** @var array<non-empty-string, list<SummaryMetricValue>> $summariesByName */
        $summariesByName = [];

        foreach ($this->summaryStorage->fetchSummaries() as $summaries) {
            $nameWithLabels = $summaries->metricNameWithLabels;

            $summariesByName[$nameWithLabels->metricName][] = $summaries;
        }

        foreach ($summariesByName as $metricName => $summaries) {
            $metric = $this->registry->getMetric($metricName, Summary::class);

            $values = [];

            foreach ($summaries as $summary) {
                 // Calculate and add quantiles
                foreach ($metric->getQuantiles() as $quantile) {
                    $quantileValue = QuantileCalculator::calculate($summary->elements, $quantile);

                    $values[] = new MetricValue(
                        new MetricNameWithLabels(
                            $metricName,
                            [
                                ...$summary->metricNameWithLabels->labels,
                                'quantile' => (string) $quantile,
                            ]
                        ),
                        $quantileValue
                    );
                }

                // Add sum
                $values[] = new MetricValue(
                    new MetricNameWithLabels(
                        "{$metricName}_sum",
                        $summary->metricNameWithLabels->labels
                    ),
                    array_sum($summary->elements)
                );

                // Add count
                $values[] = new MetricValue(
                    new MetricNameWithLabels(
                        "{$metricName}_count",
                        $summary->metricNameWithLabels->labels
                    ),
                    count($summary->elements)
                );
            }

            yield new FetchedMetric(
                $metric,
                $values
            );
        }
    }
}
