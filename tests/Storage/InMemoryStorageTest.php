<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Tests\Storage;

use PHPUnit\Framework\TestCase;
use Zlodes\PrometheusExporter\Storage\InMemoryStorage;
use Zlodes\PrometheusExporter\Storage\Storage;
use Zlodes\PrometheusExporter\Storage\StorageTesting;

class InMemoryStorageTest extends TestCase
{
    use StorageTesting;

    private function createStorage(): Storage
    {
        return new InMemoryStorage();
    }
}
