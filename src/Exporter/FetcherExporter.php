<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Exporter;

use Zlodes\PrometheusClient\Fetcher\Fetcher;

final class FetcherExporter implements Exporter
{
    public function __construct(
        private readonly Fetcher $fetcher,
    ) {
    }

    public function export(): iterable
    {
        foreach ($this->fetcher->fetch() as $fetchedMetric) {
            $metric = $fetchedMetric->metric;

            $metricStrings = [
                "# HELP {$metric->name} {$metric->help}",
                "# TYPE {$metric->name} {$metric->getPrometheusType()}",
            ];

            foreach ($fetchedMetric->values as $value) {
                $metricStrings[] = sprintf(
                    "%s%s %s",
                    $value->metricNameWithLabels->metricName,
                    $this->buildLabelsString($value->metricNameWithLabels->labels),
                    $value->value
                );
            }

            yield implode(PHP_EOL, $metricStrings);
        }
    }

    /**
     * @param array<string, string> $labels
     *
     * @return string
     */
    private function buildLabelsString(array $labels): string
    {
        if ($labels === []) {
            return '';
        }

        $formattedLabels = [];

        foreach ($labels as $labelName => $labelValue) {
            $formattedLabels[] = "$labelName=\"$labelValue\"";
        }

        return '{' . implode(',', $formattedLabels) . '}';
    }
}
