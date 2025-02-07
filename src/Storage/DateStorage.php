<?php

namespace MulerTech\FileManipulation\Storage;

use MulerTech\FileManipulation\PathManipulation;
use DateTime;
use RuntimeException;

/**
 * Class DateStorage
 *
 * Handles date-based file storage directory structure and filename generation.
 *
 * @package MulerTech\FileManipulation\Storage
 * @author Sébastien Muler
 */
readonly class DateStorage
{
    private DateTime $currentDate;

    /**
     * @param string $path Base storage path
     * @param ?DateTime $date
     */
    public function __construct(private string $path, ?DateTime $date = null)
    {
        $this->currentDate = $date ?? new DateTime();
    }

    /**
     * Create or verify all the folders for save the archive,
     * return complete path or null if error.
     * @return string
     */
    public function datePath(): string
    {
        $this->ensureDirectoryExists($this->path);
        $this->ensureDirectoryExists($this->getYearPath());
        $this->ensureDirectoryExists($this->getMonthPath());

        return $this->getMonthPath();
    }

    /**
     * Generates a filename with date prefix.
     *
     * @param string $suffix The suffix to append to the date
     * @param string $separator The separator between date and suffix
     * @return string
     */
    public static function dateFilename(string $suffix, string $separator = '-'): string
    {
        return (new DateTime())->format('Ymd') . $separator . $suffix;
    }

    /**
     * Generates a filename with date and time prefix.
     *
     * @param string $suffix The suffix to append to the datetime
     * @param string $separator The separator between datetime and suffix
     * @return string
     */
    public static function dateTimeFilename(string $suffix, string $separator = '-'): string
    {
        return (new DateTime())->format('Ymd-Hi') . $separator . $suffix;
    }

    /**
     * Ensures the base directory exists.
     *
     * @throws RuntimeException If directory creation fails
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (PathManipulation::folderExists($path)) {
            return;
        }

        PathManipulation::folderCreate($path, 0770, true);
    }

    /**
     * Gets the year directory path.
     *
     * @return string
     */
    private function getYearPath(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . $this->currentDate->format('Y');
    }

    /**
     * Gets the month directory path.
     *
     * @return string
     */
    private function getMonthPath(): string
    {
        return $this->getYearPath() . DIRECTORY_SEPARATOR . $this->currentDate->format('m');
    }
}
