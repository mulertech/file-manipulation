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
     * Returns an associative array like:
     *   ['key1' => 'value1', 'key2' => 'value2']
     * @return array<string|int, mixed>|null
     * @todo Add support for multiline values
     */
    public function parseFile(): ?array
    {
        $filename = $this->getFilename();
        if (!is_file($filename)) {
            return null;
        }

        $content = [];
        $file = new SplFileObject($filename);
        $multilineKey = null;
        $multilineValue = '';

        while (!$file->eof()) {
            $line = $file->fgets();

            // Handle multiline value ending
            if ($multilineKey !== null) {
                if ($this->isMultilineEnd($line)) {
                    $content[$multilineKey] = $multilineValue;
                    $multilineKey = null;
                    $multilineValue = '';
                    continue;
                }
                $multilineValue .= $line;
                continue;
            }

            $line = trim($line);
            if ($this->shouldSkipLine($line)) {
                continue;
            }

            // Remove inline comments after valid key/value pair is detected
            $line = $this->stripInlineComment($line);

            $equalPos = strpos($line, '=');
            if ($equalPos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $equalPos));
            $value = ltrim(substr($line, $equalPos + 1));

            // If the value signifies a multiline start, record the key and reset buffer.
            if ($this->isMultilineStart($value)) {
                $multilineKey = $key;
                $multilineValue = '';
                continue;
            }

            // If value is wrapped in quotes, unwrap them.
            if ($this->isQuoted($value)) {
                $content[$key] = $this->stripQuotes($value);
                continue;
            }

            $content[$key] = $value;
        }

        return $content;
    }

    /**
     * Load all env variables using PHP putenv.
     */
    public function loadEnv(): void
    {
        $envParsed = $this->parseFile();

        if ($envParsed !== null) {
            foreach ($envParsed as $key => $value) {
                putenv("$key=$value");
            }
        }
    }

    /**
     * Determine if the given line should be skipped.
     *
     * @param string $line
     * @return bool
     */
    private function shouldSkipLine(string $line): bool
    {
        return $line === '' || str_starts_with($line, '#');
    }

    /**
     * Remove inline comments from a line.
     * Comments that start before the '=' are not removed.
     *
     * @param string $line
     * @return string
     */
    private function stripInlineComment(string $line): string
    {
        $hashPos = strpos($line, '#');
        $equalPos = strpos($line, '=');

        if ($hashPos !== false && $equalPos !== false) {
            $line = trim(substr($line, 0, $hashPos));
        }

        return $line;
    }

    /**
     * Check if the value indicates the start of a multiline value.
     *
     * @param string $value
     * @return bool
     */
    private function isMultilineStart(string $value): bool
    {
        return str_starts_with($value, "'''") || str_starts_with($value, '"""');
    }

    /**
     * Check if the line indicates the end of a multiline value.
     *
     * @param string $line
     * @return bool
     */
    private function isMultilineEnd(string $line): bool
    {
        return str_starts_with($line, "'''") || str_starts_with($line, '"""');
    }

    /**
     * Check if the value is encapsulated in quotes.
     *
     * @param string $value
     * @return bool
     */
    private function isQuoted(string $value): bool
    {
        $firstChar = $value[0] ?? '';
        return $firstChar === '"' || $firstChar === "'";
    }

    /**
     * Remove the surrounding quotes from a quoted string.
     *
     * @param string $value
     * @return string
     */
    private function stripQuotes(string $value): string
    {
        $quote = $value[0];
        $endPos = strpos($value, $quote, 1);
        if ($endPos !== false) {
            return substr($value, 1, $endPos - 1);
        }
        return $value;
    }
}
