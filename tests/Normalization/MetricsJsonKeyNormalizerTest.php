<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Normalization;

use PHPUnit\Framework\Attributes\DataProvider;
use Zlodes\PrometheusExporter\Normalization\Exceptions\CannotNormalizeMetricsKey;
use Zlodes\PrometheusExporter\Normalization\JsonMetricKeyNormalizer;
use PHPUnit\Framework\TestCase;

final class MetricsJsonKeyNormalizerTest extends TestCase
{
    #[DataProvider('normalizerDataProvider')]
    public function testNormalizer(string $key, string $expectedName, array $expectedLabels): void
    {
        $normalizer = new JsonMetricKeyNormalizer();

        $actualKeyWithLabels = $normalizer->normalize($key);

        self::assertEquals($expectedName, $actualKeyWithLabels->metricName);
        self::assertEquals($expectedLabels, $actualKeyWithLabels->labels);
    }

    #[DataProvider('normalizationErrorsDataProvider')]
    public function testNormalizationErrors(string $key, string $expectedMessage): void
    {
        $normalizer = new JsonMetricKeyNormalizer();

        $this->expectException(CannotNormalizeMetricsKey::class);
        $this->expectExceptionMessage($expectedMessage);

        $normalizer->normalize($key);
    }

    public static function normalizerDataProvider(): iterable
    {
        yield 'empty labels' => [
            'foo',
            'foo',
            [],
        ];

        yield 'not empty labels, simple' => [
            'bar|{"baz":"qux"}',
            'bar',
            ['baz' => 'qux'],
        ];

        yield 'not empty labels, two labels' => [
            'baz|{"bar":"quux","foo":"bar"}',
            'baz',
            ['foo' => 'bar', 'bar' => 'quux'],
        ];

        yield 'not empty labels, two labels with pipe char' => [
            'baz|{"bar":"|||","foo":"bar"}',
            'baz',
            ['foo' => 'bar', 'bar' => '|||'],
        ];
    }

    public static function normalizationErrorsDataProvider(): iterable
    {
        yield 'empty string' => [
            '',
            'Expected a non-empty value',
        ];
    }
}
