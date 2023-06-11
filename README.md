# PHP Prometheus Client

[![codecov](https://codecov.io/gh/zlodes/php-prometheus-exporter/branch/master/graph/badge.svg?token=ROMQ8VBN0A)](https://codecov.io/gh/zlodes/php-prometheus-exporter)

## Why?

* Until now, there was no working Prometheus client for modern PHP
* Framework-agnostic
* Almost zero dependencies
* Won't break your business logic even if something is wrong with Metrics Storage
* Ready to use with static analysis tools (PHPStan, Psalm)

This package gives you an ability to collect and export [Prometheus](https://prometheus.io/) metrics from any modern PHP app.

Now it still doesn't support Summary.

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

- [x] Histogram metric type
- [x] Configure Semantic Release for GitHub Actions
- [ ] Summary metric type

## Simple example

```php
<?php

use Psr\Log\NullLogger;
use Zlodes\PrometheusClient\Collector\CollectorFactory;
use Zlodes\PrometheusClient\Exporter\StoredMetricsExporter;
use Zlodes\PrometheusClient\MetricTypes\Counter;
use Zlodes\PrometheusClient\MetricTypes\Gauge;
use Zlodes\PrometheusClient\MetricTypes\Histogram;
use Zlodes\PrometheusClient\Registry\ArrayRegistry;
use Zlodes\PrometheusClient\Storage\InMemoryStorage;

$registry = new ArrayRegistry();
$storage = new InMemoryStorage();

// Register your metrics
$registry
    ->registerMetric(
        new Gauge('body_temperature', 'Body temperature in Celsius')
    )
    ->registerMetric(
        new Counter('steps', 'Steps count')
    )
    ->registerMetric(
        new Histogram('request_duration', 'Request duration in seconds'),
    );

// Create a Collector factory
$collectorFactory = new CollectorFactory(
    $registry,
    $storage,
    new NullLogger(),
);

// Collect metrics
$bodyTemperatureGauge = $collectorFactory->gauge('body_temperature');

$bodyTemperatureGauge
    ->withLabels(['source' => 'armpit'])
    ->update(36.6);

$bodyTemperatureGauge
    ->withLabels(['source' => 'ass'])
    ->update(37.2);

$collectorFactory
    ->counter('steps')
    ->increment();

$requestTimer = $collectorFactory
    ->histogram('request_duration')
    ->startTimer();

usleep(50_000);

$requestTimer->stop();

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

#### Keys serialization

There is a [Serializer](PrometheusClient/KeySerialization/Serializer.php) interface (with JSON-based implementation) to simplify work with a key-value storage.

Example can be found in [InMemoryStorage](PrometheusClient/Storage/InMemoryStorage.php).

#### Storage Testing

There is a simple [trait](PrometheusClient/Storage/StorageTesting.php) to tests any storage you want. Here is an example:

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
