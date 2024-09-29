<?php

namespace MulerTech\FileManipulation\FileType;

use MulerTech\FileManipulation\FileManipulation;
use SplFileObject;

/**
 * Class Env
 * @package MulerTech\FileManipulation\FileType
 * @author Sébastien Muler
 */
class Env extends FileManipulation
{
    /**
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);
    }

    /**
     * Parse env file from a file like :
     * key1=value1
     * #Some comments
     * key2="value2"
     *
     * To this type of array :
     * ['key1' => 'value1', 'key2' => 'value2']
     * @return array|null
     */
    public function parseFile(): ?array
    {
        if (!is_file($filename = $this->getFilename())) {
            return null;
        }

        $content = [];
        $file = new SplFileObject($filename);
        while (!$file->eof()) {
            $line = trim($file->fgets());
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (($equal = strpos($line, '=')) === false) {
                continue;
            }

            $firstPart = substr($line, 0, $equal);
            $secondPart = substr($line, $equal + 1);

            if ($secondPart === '' || mb_strlen($firstPart) === 1) {
                $content[$firstPart] = $secondPart;
                continue;
            }

            $firstChar = $secondPart[0];
            $lastChar = substr($secondPart, -1);

            if (($firstChar === '"' && $lastChar === '"') || ($firstChar === "'" && $lastChar === "'")) {
                $content[$firstPart] = substr($secondPart, 1, -1);
                continue;
            }

            $content[$firstPart] = $secondPart;
        }

        $file = null;

        return $content;
    }

    /**
     * Set all the environment key => value
     */
    public function loadEnv(): void
    {
        $envParsed = $this->parseFile();

        if (is_null($envParsed)) {
            return;
        }

        foreach ($envParsed as $key => $value) {
            putenv("$key=$value");
        }
    }
}