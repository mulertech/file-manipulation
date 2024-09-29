<?php

namespace MulerTech\FileManipulation\Storage;

use MulerTech\FileManipulation\PathManipulation;

/**
 * Class DateStorage
 * @package MulerTech\FileManipulation\Storage
 * @author Sébastien Muler
 */
class DateStorage
{
    public function __construct(private readonly string $path)
    {}

    /**
     * Create or verify all the folders for save the archive,
     * return complete path or null if error.
     * @return string|null
     */
    public function datePath(): ?string
    {
        $path = $this->path;

        //archive directory
        if (!(PathManipulation::folderExists($path) || PathManipulation::folderCreate($path))) {
            return null;
        }

        //year directory
        if (!($this->yearExists($path) || $this->yearCreate($path))) {
            return null;
        }

        //month directory
        if (!($this->monthExists($path) || $this->monthCreate($path))) {
            return null;
        }

        //complete filename path
        return $path . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m");
    }

    /**
     * @param string $suffix
     * @param string $separator
     * @return string
     */
    public static function dateFilename(string $suffix, string $separator = '-'): string
    {
        return date('Ymd') . $separator . $suffix;
    }

    /**
     * @param string $suffix
     * @param string $separator
     * @return string
     */
    public static function dateTimeFilename(string $suffix, string $separator = '-'): string
    {
        return date('Ymd-Hi') . $separator . $suffix;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function yearExists(string $path): bool
    {
        return is_dir($path . DIRECTORY_SEPARATOR . date("Y"));
    }

    /**
     * @param string $path
     * @return bool
     */
    private function yearCreate(string $path): bool
    {
        return PathManipulation::folderCreate($path . DIRECTORY_SEPARATOR . date("Y"));
    }

    /**
     * @param string $path
     * @return bool
     */
    private function monthExists(string $path): bool
    {
        return is_dir($path . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m"));
    }

    /**
     * @param string $path
     * @return bool
     */
    private function monthCreate(string $path): bool
    {
        return PathManipulation::folderCreate($path . DIRECTORY_SEPARATOR . date("Y") . DIRECTORY_SEPARATOR . date("m"));
    }
}