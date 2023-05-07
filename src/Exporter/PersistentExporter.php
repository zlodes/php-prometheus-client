<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Exporter;

use Psr\Log\LoggerInterface;
use Zlodes\PrometheusExporter\Normalization\Contracts\MetricKeyNormalizer;
use Zlodes\PrometheusExporter\Registry\Registry;
use Zlodes\PrometheusExporter\Storage\Storage;

final class PersistentExporter implements Exporter
{
    public function __construct(
        public readonly Registry $registry,
        public readonly Storage $storage,
        public readonly MetricKeyNormalizer $keyNormalizer,
        public readonly LoggerInterface $logger,
    ) {
    }

    public function export(): iterable
    {
        $valuesRaw = $this->storage->fetch();

        /** @var array<string, list<array{0: array<string, string>, 1: float}>> $normalized */
        $normalized = [];

        foreach ($valuesRaw as $key => $value) {
            $nameWithLabels = $this->keyNormalizer->normalize($key);

            $normalized[$nameWithLabels->metricName][] = [
                $nameWithLabels->labels,
                $value,
            ];
        }

        foreach ($this->registry->getAll() as $name => $metric) {
            $prometheusString = "# HELP $name {$metric->getHelp()}\n";
            $prometheusString .= "# TYPE $name {$metric->getType()->value}\n";

            $values = $normalized[$name] ?? null;

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

            foreach ($values as $index => [$labels, $value]) {
                $prometheusString .= sprintf(
                    "%s%s %s",
                    $name,
                    $this->buildLabelsString($labels),
                    $value
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
