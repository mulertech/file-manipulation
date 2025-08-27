<?php

namespace MulerTech\FileManipulation\FileType;

use MulerTech\FileManipulation\FileManipulation;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Class Yaml
 * @package MulerTech\FileManipulation\FileType
 * @author Sébastien Muler
 */
class Yaml extends FileManipulation
{
    private const array EXTENSION = ['yml', 'yaml'];

    /**
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename, self::EXTENSION);
    }

    /**
     * @inheritDoc
     */
    public function openFile(): mixed
    {
        $fileContent = $this->getFileContent();

        if ($fileContent === null) {
            return null;
        }

        return function_exists('yaml_parse') ? yaml_parse($fileContent) : SymfonyYaml::parse($fileContent);
    }

    /**
     * @inheritDoc
     */
    public function saveFile(mixed $content, bool $recursive = false): bool
    {
        $fileContent = function_exists('yaml_emit') ? yaml_emit($content) : SymfonyYaml::dump($content);

        return $this->filePutContents($fileContent, $recursive);
    }
}
