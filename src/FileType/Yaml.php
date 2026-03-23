<?php

namespace MulerTech\FileManipulation\FileType;

use MulerTech\FileManipulation\FileManipulation;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Class Yaml.
 *
 * @author Sébastien Muler
 */
class Yaml extends FileManipulation
{
    private const array EXTENSION = ['yml', 'yaml'];

    public function __construct(string $filename)
    {
        parent::__construct($filename, self::EXTENSION);
    }

    public function openFile(): mixed
    {
        $fileContent = $this->getFileContent();

        if (null === $fileContent) {
            return null;
        }

        return function_exists('yaml_parse') ? yaml_parse($fileContent) : SymfonyYaml::parse($fileContent);
    }

    public function saveFile(mixed $content, bool $recursive = false): bool
    {
        $fileContent = function_exists('yaml_emit') ? yaml_emit($content) : SymfonyYaml::dump($content);

        return $this->filePutContents($fileContent, $recursive);
    }
}
