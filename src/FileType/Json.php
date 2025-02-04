<?php

namespace MulerTech\FileManipulation\FileType;

use JsonException;
use MulerTech\FileManipulation\FileManipulation;

/**
 * Class Json
 * @package MulerTech\FileManipulation\FileType
 * @author Sébastien Muler
 */
class Json extends FileManipulation
{
    private const string EXTENSION = 'json';

    /**
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename, self::EXTENSION);
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function openFile(): mixed
    {
        $fileContent = $this->getFileContent();

        if ($fileContent === null) {
            return null;
        }

        return json_decode($fileContent, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function saveFile(mixed $content, bool $recursive = false): bool
    {
        return $this->filePutContents(json_encode($content, JSON_THROW_ON_ERROR), $recursive);
    }
}
