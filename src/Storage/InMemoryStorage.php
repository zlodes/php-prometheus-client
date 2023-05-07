<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

final class InMemoryStorage implements Storage
{
    /** @var array<non-empty-string, float|int> */
    private array $storage = [];

    public function fetch(): array
    {
        return $this->storage;
    }

    public function flush(): void
    {
        $this->storage = [];
    }

    public function getValue(string $key): float|int
    {
        return $this->storage[$key] ?? 0;
    }

    public function setValue(string $key, float|int $value): void
    {
        $this->storage[$key] = $value;
    }

    public function incrementValue(string $key, float|int $value): void
    {
        if (!array_key_exists($key, $this->storage)) {
            $this->storage[$key] = $value;

            return;
        }

        $this->storage[$key] += $value;
    }
}
