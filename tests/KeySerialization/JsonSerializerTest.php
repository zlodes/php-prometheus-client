<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\KeySerialization;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusClient\Exceptions\MetricKeySerializationException;
use Zlodes\PrometheusClient\Exceptions\MetricKeyUnserializationException;
use Zlodes\PrometheusClient\KeySerialization\JsonSerializer;
use Zlodes\PrometheusClient\Storage\DTO\MetricNameWithLabels;

class JsonSerializerTest extends TestCase
{
    #[DataProvider('serializeDataProvider')]
    public function testSerialization(string $name, array $labels, string $expectedKey): void
    {
        $serializer = new JsonSerializer();
        $nameWithLabels = new MetricNameWithLabels($name, $labels);

        $actualKey = $serializer->serialize($nameWithLabels);

        self::assertEquals($expectedKey, $actualKey);
    }

    public function testSerializationError(): void
    {
        $serializer = new JsonSerializer();
        $nameWithLabels = new MetricNameWithLabels('foo', ["foo" => "\xB1\x31"]);

        $this->expectException(MetricKeySerializationException::class);
        $this->expectExceptionMessage('JSON encoding error');

        $serializer->serialize($nameWithLabels);
    }

    #[DataProvider('unserializeDataProvider')]
    public function testUnserialize(string $key, string $expectedName, array $expectedLabels): void
    {
        $serializer = new JsonSerializer();

        $actualKeyWithLabels = $serializer->unserialize($key);

        self::assertEquals($expectedName, $actualKeyWithLabels->metricName);
        self::assertEquals($expectedLabels, $actualKeyWithLabels->labels);
    }

    #[DataProvider('unserializationErrorsDataProvider')]
    public function testUnserializationErrors(string $key, string $expectedMessage): void
    {
        $serializer = new JsonSerializer();

        $this->expectException(MetricKeyUnserializationException::class);
        $this->expectExceptionMessage($expectedMessage);

        $serializer->unserialize($key);
    }

    public static function unserializeDataProvider(): iterable
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

    public static function unserializationErrorsDataProvider(): iterable
    {
        yield 'empty string' => [
            '',
            'Expected a non-empty value',
        ];
    }

    public static function serializeDataProvider(): iterable
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
