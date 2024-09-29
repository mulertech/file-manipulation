<?php

namespace MulerTech\FileManipulation;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * Class PathManipulation
 * @package MulerTech\FileManipulation
 * @author Sébastien Muler
 */
class PathManipulation
{
    /**
     * @param string $folder
     * @return bool
     */
    public static function folderExists(string $folder): bool
    {
        return is_dir($folder);
    }

    /**
     * @param string $folder
     * @param string|null $last
     * @return string|null
     */
    public static function firstExistingParentFolder(string $folder, string $last = null): ?string
    {
        $parent = dirname($folder);

        // Prevent infinite loop
        if ($parent === $last) {
            return null;
        }

        if (is_dir($parent)) {
            return $parent;
        }

        return self::firstExistingParentFolder($parent, $parent);
    }

    /**
     * @param string $folder
     * @param int $mode
     * @param bool $recursive
     * @return bool
     */
    public static function folderCreate(string $folder, int $mode = 0770, bool $recursive = false): bool
    {
        if (is_dir($folder)) {
            return true;
        }
        $parent = $recursive ? self::firstExistingParentFolder($folder) : dirname($folder);

        if ($parent === null) {
            throw new RuntimeException(sprintf('The parent folder of "%s" does not exist.', $folder));
        }

        if (is_writable($parent)) {
            return mkdir($folder, $mode, $recursive) || is_dir($folder);
        }

        throw new RuntimeException(
            sprintf(
                'Unable to create the path "%s", the parent folder "%s" is write protected.',
                $folder,
                $parent
            )
        );
    }

    /**
     * @param string $folder
     * @return bool
     */
    public static function folderDelete(string $folder): bool
    {
        return !is_dir($folder) || rmdir($folder);
    }

    /**
     * @param string $path
     * @param bool $recursive
     * @return array
     */
    public static function fileList(string $path, bool $recursive = true): array {
        return ($recursive === true) ? self::recursiveIteratorFileList($path) : self::iteratorFileList($path);
    }

    /**
     * @param $path
     * @return array
     */
    private static function recursiveIteratorFileList($path): array
    {
        $list = [];
        $directory = new RecursiveDirectoryIterator($path);
        foreach (new RecursiveIteratorIterator($directory) as $item) {
            if (!in_array($item->getFilename(), ['.', '..'], true)) {
                $list[] = $item->getPathname();
            }
        }
        return $list;
    }

    /**
     * @param $path
     * @return array
     */
    private static function iteratorFileList($path): array
    {
        $list = [];
        foreach (new DirectoryIterator($path) as $item) {
            if (!$item->isDot() && !$item->isDir()) {
                $list[] = $item->getPathname();
            }
        }
        return $list;
    }
}