# PHP Prometheus Exporter

[![codecov](https://codecov.io/gh/zlodes/php-prometheus-exporter/branch/master/graph/badge.svg?token=ROMQ8VBN0A)](https://codecov.io/gh/zlodes/php-prometheus-exporter)

## Why?

* Until now, there was no working Prometheus Exporter for modern PHP
* Framework-agnostic
* Almost zero dependencies
* Ready to use with static analysis tools (Psalm)

This package gives you an ability to collect and export [Prometheus](https://prometheus.io/) metrics from any modern PHP app.

Now supports only Counter and Gauge metric types.

> **Warning**
> This package is still in development. Use it on your own risk until 1.0.0 release.

## Adapters

* For Laravel: [zlodes/prometheus-exporter-laravel](https://github.com/zlodes/php-prometheus-exporter-laravel)

## Installation

```shell
composer require zlodes/prometheus-exporter
```

## Class responsibilities

| Interface                             | Description                  | Default implementation                                          |
|---------------------------------------|------------------------------|-----------------------------------------------------------------|
| [Registry](src/Registry/Registry.php) | To declare a specific metric | [ArrayRegistry](src/Registry/ArrayRegistry.php)                 |
| [Storage](src/Storage/Storage.php)    | Metrics values storage       | [InMemoryStorage](src/Storage/InMemoryStorage.php)              |
| [Exporter](src/Exporter/Exporter.php) | Output collected metrics     | [StoredMetricsExporter](src/Exporter/StoredMetricsExporter.php) |

Each class should be registered as a service. As a `singleton` in Laravel or `shared` service in Symfony.

## Roadmap

- [ ] Histogram metric type
- [ ] Summary metric type
- [ ] Configure Semantic Release for GitHub Actions

## Simple example

```php
<?php

use Psr\Log\NullLogger;
use Zlodes\PrometheusExporter\Collector\CollectorFactory;
use Zlodes\PrometheusExporter\Exporter\StoredMetricsExporter;
use Zlodes\PrometheusExporter\MetricTypes\Counter;
use Zlodes\PrometheusExporter\MetricTypes\Gauge;
use Zlodes\PrometheusExporter\Registry\ArrayRegistry;
use Zlodes\PrometheusExporter\Storage\InMemoryStorage;

$registry = new ArrayRegistry();
$storage = new InMemoryStorage();

// Register your metrics
$registry
    ->registerMetric(
        new Gauge('body_temperature', 'Body temperature in Celsius')
    )
    ->registerMetric(
        new Counter('steps', 'Steps count')
    );

// Create a Collector
$collectorFactory = new CollectorFactory(
    $registry,
    $storage,
    new NullLogger(),
);

// Collect metrics
$bodyTemperatureGauge = $collectorFactory->gauge('body_temperature');

$bodyTemperatureGauge
    ->withLabels(['source' => 'armpit'])
    ->setValue(36.6);

$bodyTemperatureGauge
    ->withLabels(['source' => 'ass'])
    ->setValue(37.2);

$collectorFactory
    ->counter('steps')
    ->increment();

// Export metrics
$exporter = new StoredMetricsExporter(
    $registry,
    $storage,
    new NullLogger(),
);

foreach ($exporter->export() as $metricOutput) {
    echo $metricOutput . "\n\n";
}
```

Output example:
```
# HELP body_temperature Body temperature in Celsius
# TYPE body_temperature gauge
body_temperature{source="armpit"} 36.6
body_temperature{source="ass"} 37.2

# HELP steps Steps count
# TYPE steps counter
steps 1
```

## Testing

### Run tests

```shell
php ./vendor/bin/phpunit
```

### Creating your own Storage

#### Keys normalization/denormalization

There are two interfaces (with JSON-based implementations) to simplify work with a key-value storage:

* [MetricKeyNormalizer](src/Normalization/Contracts/MetricKeyNormalizer.php)
* [MetricKeyDenormalizer](src/Normalization/Contracts/MetricKeyDenormalizer.php)

Example can be found in [InMemoryStorage](src/Storage/InMemoryStorage.php).

#### Storage Testing

There is a simple [trait](src/Storage/StorageTesting.php) to tests any storage you want. Here is an example:

```php
class InMemoryStorageTest extends TestCase
{
    use StorageTesting;

    private function createStorage(): Storage
    {
        return new InMemoryStorage();
    }
}
```
