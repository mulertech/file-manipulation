<?php

namespace MulerTech\FileManipulation\FileType;

use MulerTech\FileManipulation\FileManipulation;

/**
 * Class Json.
 *
 * @author Sébastien Muler
 */
class Json extends FileManipulation
{
    private const string EXTENSION = 'json';

    public function __construct(string $filename)
    {
        parent::__construct($filename, self::EXTENSION);
    }

    /**
     * @throws \JsonException
     */
    public function openFile(): mixed
    {
        $fileContent = $this->getFileContent();

        if (null === $fileContent) {
            return null;
        }

        return json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \JsonException
     */
    public function saveFile(mixed $content, bool $recursive = false): bool
    {
        return $this->filePutContents(json_encode($content, JSON_THROW_ON_ERROR), $recursive);
    }
}
