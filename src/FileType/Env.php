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
     * @return array<string|int, mixed>|null
     * @todo Add support for multiline values
     */
    public function parseFile(): ?array
    {
        if (!is_file($filename = $this->getFilename())) {
            return null;
        }

        $content = [];
        $file = new SplFileObject($filename);
        $currentKey = null;
        $currentValue = '';

        while (!$file->eof()) {
            $line = $file->fgets();

            if ($currentKey !== null) {
                if (str_starts_with($line, "'''") || str_starts_with($line, '"""')) {
                    $content[$currentKey] = $currentValue;
                    $currentKey = null;
                    $currentValue = '';
                    continue;
                }

                $currentValue .= $line;
                continue;
            }

            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $hash = strpos($line, '#');

            if (($equal = strpos($line, '=')) === false) {
                continue;
            }

            if (is_int($hash) && $hash < $equal) {
                continue;
            }

            if ($hash) {
                $line = trim(substr($line, 0, $hash));
            }

            $firstPart = substr($line, 0, $equal);
            $secondPart = substr($line, $equal + 1);

            if ($secondPart === '' || mb_strlen($firstPart) === 1) {
                $content[$firstPart] = $secondPart;
                continue;
            }

            $firstChar = $secondPart[0];

            if (str_starts_with($secondPart, "'''") || str_starts_with($secondPart, '"""')) {
                $currentKey = $firstPart;
                $currentValue = '';
                continue;
            }

            if ($firstChar === '"' || $firstChar === "'") {
                $quote = $firstChar;
                $secondQuote = strpos($secondPart, $quote, 1);

                $content[$firstPart] = trim(substr($secondPart, 0, $secondQuote + 1), $quote);
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

        if (!is_null($envParsed)) {
            foreach ($envParsed as $key => $value) {
                putenv("$key=$value");
            }
        }

    }
}
