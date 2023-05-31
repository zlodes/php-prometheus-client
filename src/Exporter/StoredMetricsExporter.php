<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Exporter;

use Zlodes\PrometheusExporter\Registry\Registry;
use Zlodes\PrometheusExporter\Storage\DTO\MetricValue;
use Zlodes\PrometheusExporter\Storage\Storage;

final class StoredMetricsExporter implements Exporter
{
    public function __construct(
        private readonly Registry $registry,
        private readonly Storage $storage,
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
            $metricStrings = [
                "# HELP $name {$metric->getHelp()}",
                "# TYPE $name {$metric->getType()->value}",
            ];

            $metricNameWithDependant = [
                $metric->getName(),
                ...$metric->getDependentMetrics(),
            ];

            foreach ($metricNameWithDependant as $metricName) {
                foreach ($valuesGroupedByMetric[$metricName] ?? [] as $value) {
                    $metricStrings[] = sprintf(
                        "%s%s %s",
                        $metricName,
                        $this->buildLabelsString($value->metricNameWithLabels->labels),
                        $value->value
                    );
                }
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
