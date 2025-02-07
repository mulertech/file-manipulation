<?php

namespace MulerTech\FileManipulation;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use FilesystemIterator;

/**
 * Class PathManipulation
 * @package MulerTech\FileManipulation
 * @author Sébastien Muler
 */
final class PathManipulation
{
    private const int DEFAULT_FOLDER_MODE = 0770;

    /**
     * @param string $folder
     * @return bool
     */
    public static function folderExists(string $folder): bool
    {
        return is_dir($folder);
    }

    /**
     * Find the first existing parent folder in the directory tree
     *
     * @param string $folder The folder path to check
     * @return string The path of the first existing parent folder
     */
    public static function firstExistingParentFolder(string $folder): string
    {
        $parent = dirname($folder);

        if (self::folderExists($parent)) {
            return $parent;
        }

        return self::firstExistingParentFolder($parent);
    }

    /**
     * Create a folder with specified permissions
     *
     * @param string $folder The folder path to create
     * @param int $mode Permission mode for the new folder
     * @param bool $recursive Whether to create parent directories if they don't exist
     * @return bool True if folder was created or already exists
     * @throws RuntimeException If parent folder is not writable
     */
    public static function folderCreate(
        string $folder,
        int $mode = self::DEFAULT_FOLDER_MODE,
        bool $recursive = false
    ): bool {
        if (self::folderExists($folder)) {
            return true;
        }

        $parent = $recursive ? self::firstExistingParentFolder($folder) : dirname($folder);

        if (!is_writable($parent)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to create the path "%s", the parent folder "%s" is write protected.',
                    $folder,
                    $parent
                )
            );
        }

        return mkdir($folder, $mode, $recursive) || is_dir($folder);
    }

    /**
     * Delete a folder if it exists
     *
     * @param string $folder The folder path to delete
     * @return bool True if folder was deleted or didn't exist
     */
    public static function folderDelete(string $folder): bool
    {
        return !self::folderExists($folder) || rmdir($folder);
    }

    /**
     * Get a list of files in a directory
     *
     * @param string $path The directory path to list
     * @param bool $recursive Whether to include files in subdirectories
     * @return array<int, string> List of file paths
     */
    public static function fileList(string $path, bool $recursive = true): array
    {
        return $recursive ? self::recursiveIteratorFileList($path) : self::iteratorFileList($path);
    }

    /**
     * Get a recursive list of files in a directory
     *
     * @param string $path The directory path to list
     * @return array<int, string> List of file paths
     */
    private static function recursiveIteratorFileList(string $path): array
    {
        $directory = new RecursiveDirectoryIterator(
            $path,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
        );

        $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

        $files = [];
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }

    /**
     * Get a non-recursive list of files in a directory
     *
     * @param string $path The directory path to list
     * @return array<int, string> List of file paths
     */
    private static function iteratorFileList(string $path): array
    {
        $iterator = new DirectoryIterator($path);
        $files = [];

        foreach ($iterator as $item) {
            if (!$item->isDot() && $item->isFile()) {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }
}
