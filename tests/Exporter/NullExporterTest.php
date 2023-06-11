<?php

declare(strict_types=1);

namespace Zlodes\PrometheusClient\Tests\Exporter;

use Zlodes\PrometheusClient\Exporter\NullExporter;
use PHPUnit\Framework\TestCase;

class NullExporterTest extends TestCase
{
    public function testExport(): void
    {
        $exporter = new NullExporter();

        self::assertEquals([], $exporter->export());
    }
}
