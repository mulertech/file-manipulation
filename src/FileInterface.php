<?php

namespace MulerTech\FileManipulation;

/**
 * Interface FileInterface
 * @package MulerTech\FileManipulation\NonRelational
 * @author Sébastien Muler
 */
interface FileInterface
{
    /**
     * @return string|array<int, string>
     */
    public function getExtension(): string|array;

    /**
     * @return mixed
     */
    public function openFile(): mixed;

    /**
     * @param mixed $content
     * @return bool True if success.
     */
    public function saveFile(mixed $content): bool;

    /**
     * @param mixed $content
     * @param bool $recursive
     * @return bool
     */
    public function filePutContents(mixed $content, bool $recursive = false): bool;
}
