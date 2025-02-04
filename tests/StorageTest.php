<?php

namespace MulerTech\FileManipulation\Tests;

use MulerTech\FileManipulation\Storage\DateStorage;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    private const string LOGS_DIRECTORY = __DIR__ . DIRECTORY_SEPARATOR . 'Files' . DIRECTORY_SEPARATOR . 'Storage' . DIRECTORY_SEPARATOR . 'Logs';

    protected function setUp(): void
    {
        parent::setUp();
        //Clean the FakeLogs directory
        $this->deleteDirContent(self::LOGS_DIRECTORY);
        rmdir(self::LOGS_DIRECTORY);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        //Clean the FakeLogs directory
        $this->deleteDirContent(self::LOGS_DIRECTORY);
        rmdir(self::LOGS_DIRECTORY);
    }

    private function deleteDirContent(string $directory): void
    {
        if (is_dir($directory) && $toDelete = scandir($directory)) {
            foreach ($toDelete as $dir) {
                if ($dir !== '.' && $dir !== '..') {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $dir)) {
                        $this->deleteDirContent($directory . DIRECTORY_SEPARATOR . $dir);
                        rmdir($directory . DIRECTORY_SEPARATOR . $dir);
                    } else {
                        unlink($directory . DIRECTORY_SEPARATOR . $dir);
                    }
                }
            }
        }
    }

    // Test DateStorage
    public function testDatePath(): void
    {
        $dateStorage = new DateStorage(self::LOGS_DIRECTORY);
        self::assertEquals(
            self::LOGS_DIRECTORY . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m"),
            $dateStorage->datePath()
        );
    }

    public function testDateFilename(): void
    {
        self::assertEquals(date('Ymd') . '-' . 'test', DateStorage::dateFilename('test'));
    }

    public function testDateTimeFilename(): void
    {
        self::assertEquals(date('Ymd-Hi') . '-' . 'test', DateStorage::dateTimeFilename('test'));
    }
}