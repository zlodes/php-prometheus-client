<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Normalization;

use PHPUnit\Framework\Attributes\DataProvider;
use Zlodes\PrometheusExporter\DTO\MetricNameWithLabels;
use Zlodes\PrometheusExporter\Normalization\Exceptions\CannotDenormalizeMetricsKey;
use Zlodes\PrometheusExporter\Normalization\JsonMetricKeyDenormalizer;
use PHPUnit\Framework\TestCase;

final class JsonMetricKeyDenormalizerTest extends TestCase
{
    #[DataProvider('denormalizerDataProvider')]
    public function testDenormalizer(string $name, array $labels, string $expectedKey): void
    {
        $denormalizer = new JsonMetricKeyDenormalizer();
        $nameWithLabels = new MetricNameWithLabels($name, $labels);

        $actualKey = $denormalizer->denormalize($nameWithLabels);

        self::assertEquals($expectedKey, $actualKey);
    }

    public function testDenormalizationError(): void
    {
        $denormalizer = new JsonMetricKeyDenormalizer();
        $nameWithLabels = new MetricNameWithLabels('foo', ["foo" => "\xB1\x31"]);

        $this->expectException(CannotDenormalizeMetricsKey::class);
        $this->expectExceptionMessage('JSON encoding error');

        $denormalizer->denormalize($nameWithLabels);
    }

    public static function denormalizerDataProvider(): iterable
    {
        yield 'empty labels' => [
            'foo',
            [],
            'foo',
        ];

        yield 'not empty labels, simple' => [
            'bar',
            ['baz' => 'qux'],
            'bar|{"baz":"qux"}',
        ];

        yield 'not empty labels, two labels' => [
            'baz',
            ['foo' => 'bar', 'bar' => 'quux'],
            'baz|{"bar":"quux","foo":"bar"}',
        ];

        yield 'not empty labels, two labels, different order' => [
            'baz',
            ['bar' => 'quux', 'foo' => 'bar'],
            'baz|{"bar":"quux","foo":"bar"}',
        ];
    }
}
