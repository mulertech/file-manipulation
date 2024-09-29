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
     * @return string|array
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

}