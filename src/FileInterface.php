<?php

namespace MulerTech\FileManipulation;

/**
 * Interface FileInterface.
 *
 * @author Sébastien Muler
 */
interface FileInterface
{
    /**
     * @return string|array<int, string>
     */
    public function getExtension(): string|array;

    public function openFile(): mixed;

    /**
     * @return bool true if success
     */
    public function saveFile(mixed $content): bool;

    public function filePutContents(mixed $content, bool $recursive = false): bool;
}
