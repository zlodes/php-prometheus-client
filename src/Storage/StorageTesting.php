<?php

declare(strict_types=1);

namespace Zlodes\PrometheusExporter\Storage;

use Ramsey\Uuid\Uuid;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

trait StorageTesting
{
    abstract private function createStorage(): Storage;
    
    public function testGetSet(): void
    {
        $storage = $this->createStorage();

        $key = Uuid::uuid4()->toString();

        assertEquals(0, $storage->getValue($key));

        $storage->setValue($key, 42.69);
        assertEquals(42.69, $storage->getValue($key));

        $storage->incrementValue($key, 7.31);
        assertEquals(50, $storage->getValue($key));

        $storage->incrementValue($key, -2.5);
        assertEquals(47.5, $storage->getValue($key));
    }

    public function testGetAllAndFlush(): void
    {
        $storage = $this->createStorage();

        $storage->flush();
        assertEmpty($storage->fetch());

        $storage->setValue('foo', 0);
        $storage->setValue('bar', 3.14);
        assertEquals(['foo' => 0, 'bar' => 3.14], $storage->fetch());

        $storage->flush();
        assertEmpty($storage->fetch());
    }
}
