<?php

namespace MulerTech\FileManipulation\Tests;

use MulerTech\FileManipulation\PathManipulation;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PathManipulationTest extends TestCase
{
    private const string DS = DIRECTORY_SEPARATOR;

    public function testFolderExists(): void
    {
        self::assertTrue(PathManipulation::folderExists(__DIR__ . self::DS . 'Files'));
        self::assertFalse(
            PathManipulation::folderExists(__DIR__ . self::DS . 'Files' . self::DS . 'Nope')
        );
    }

    public function testFirstExistingParentFolder(): void
    {
        self::assertEquals(
            __DIR__ . self::DS . 'Files',
            PathManipulation::firstExistingParentFolder(
                __DIR__ . self::DS . 'Files' . self::DS . 'NopeFolder' . self::DS . 'NopeChildrenFolder'
            )
        );
    }

    public function testFolderCreateAndDelete(): void
    {
        self::assertTrue(PathManipulation::folderCreate(__DIR__));
        self::assertTrue(
            PathManipulation::folderCreate(__DIR__ . self::DS . 'Files' . self::DS . 'NewFolder')
        );
        self::assertTrue(
            PathManipulation::folderExists(__DIR__ . self::DS . 'Files' . self::DS . 'NewFolder')
        );
        self::assertTrue(
            PathManipulation::folderDelete(__DIR__ . self::DS . 'Files' . self::DS . 'NewFolder')
        );
    }

    public function testFolderCreateRecursiveAndDelete(): void
    {
        self::assertTrue(
            PathManipulation::folderCreate(
                __DIR__ . self::DS . 'Files' . self::DS . 'NewFolder' . self::DS . 'NewSubFolder',
                0770,
                true
            )
        );
        self::assertTrue(
            PathManipulation::folderExists(
                __DIR__ . self::DS . 'Files' . self::DS . 'NewFolder' . self::DS . 'NewSubFolder'
            )
        );
        self::assertTrue(
            PathManipulation::folderDelete(
                __DIR__ . self::DS . 'Files' . self::DS . 'NewFolder' . self::DS . 'NewSubFolder'
            )
        );
        self::assertTrue(
            PathManipulation::folderDelete(__DIR__ . self::DS . 'Files' . self::DS . 'NewFolder')
        );
    }

    public function testFolderCreateAndThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        PathManipulation::folderCreate(__DIR__ . self::DS . 'Files' . self::DS . 'Nope' . self::DS . 'Nope');
    }

    public function testFolderDeleteAndThrowException(): void
    {
        self::assertTrue(
            PathManipulation::folderDelete(__DIR__ . self::DS . 'Files' . self::DS . 'Nope')
        );
    }

    public function testFileList(): void
    {
        $dir = __DIR__ . self::DS . 'Files' . self::DS . 'FileList';
        $list[] = $dir . self::DS . 'yamlTest1.yaml';
        self::assertEquals($list, PathManipulation::fileList($dir, false));
    }

    public function testFileListRecursive(): void
    {
        $dir = __DIR__ . self::DS . 'Files' . self::DS . 'FileList';
        $list[] = $dir . self::DS . 'path1' . self::DS . 'path12' . self::DS . 'path121' . self::DS . 'yamlTest2.yaml';
        $list[] = $dir . self::DS . 'yamlTest1.yaml';
        self::assertEquals($list, PathManipulation::fileList($dir));
    }
}