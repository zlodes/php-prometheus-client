<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

interface Storage
{
    /**
     * @return array<non-empty-string, float|int> Metrics array with denormalized keys and values
     */
    public function fetch(): array;

    /**
     * Removes all the keys
     */
    public function flush(): void;

    /**
     * @param non-empty-string $key
     *
     * @return float|int
     */
    public function getValue(string $key): float|int;

    /**
     * @param non-empty-string $key
     * @param float|int $value
     *
     * @return void
     */
    public function setValue(string $key, float|int $value): void;

    /**
     * @param non-empty-string $key
     * @param float|int $value
     *
     * @return void
     */
    public function incrementValue(string $key, float|int $value): void;
}
