<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Exporter;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusExporter\Registry\Registry;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;
use Zlodes\PrometheusExporter\Storage\Storage;

final class StoredMetricsExporter implements Exporter
{
    public function __construct(
        public readonly Registry $registry,
        public readonly Storage $storage,
        public readonly LoggerInterface $logger,
    ) {
    }

    public function export(): iterable
    {
        /** @var array<string, list<MetricValue>> $valuesGroupedByMetric */
        $valuesGroupedByMetric = [];

        foreach ($this->storage->fetch() as $value) {
            $valuesGroupedByMetric[$value->metricNameWithLabels->metricName][] = $value;
        }

        foreach ($this->registry->getAll() as $name => $metric) {
            $prometheusString = "# HELP $name {$metric->getHelp()}\n";
            $prometheusString .= "# TYPE $name {$metric->getType()->value}\n";

            $values = $valuesGroupedByMetric[$name] ?? null;

            // Yield single metric with default labels and initial value
            if ($values === null) {
                $prometheusString .= sprintf(
                    "%s%s %s",
                    $name,
                    $this->buildLabelsString($metric->getInitialLabels()),
                    0
                );

                yield $prometheusString;

                continue;
            }

            $valuesCount = count($values);

            foreach ($values as $index => $value) {
                $prometheusString .= sprintf(
                    "%s%s %s",
                    $name,
                    $this->buildLabelsString($value->metricNameWithLabels->labels),
                    $value->value
                );

                if ($index < $valuesCount - 1) {
                    $prometheusString .= "\n";
                }
            }

            yield $prometheusString;
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
