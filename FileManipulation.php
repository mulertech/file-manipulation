<?php

namespace MulerTech\FileManipulation;

use RuntimeException;
use SplFileObject;

/**
 * Class FileManipulation
 * @package MulerTech\FileManipulation
 * @author Sébastien Muler
 */
class FileManipulation implements FileInterface
{
    /**
     * @var string
     */
    private string $filename;
    /**
     * @var string|array
     */
    protected string|array $extension = '';

    /**
     * Json constructor.
     * @param string $filename
     * @param string|array $extension
     */
    public function __construct(string $filename, string|array $extension = '')
    {
        $this->filename = $filename;
        $this->extension = $extension;
    }

    /**
     * @return bool
     */
    public function checkExtension(): bool
    {
        $extension = pathinfo($this->filename, PATHINFO_EXTENSION);

        if (is_string($this->extension) && $extension !== $this->extension) {
            throw new RuntimeException(
                sprintf(
                    'Class FileManipulation, function checkExtension. The given filename does not have the %s extension.',
                    $this->extension
                )
            );
        }

        if (is_array($this->extension) && !in_array($extension, $this->extension, true)) {
            throw new RuntimeException(
                sprintf(
                    'Class FileManipulation, function checkExtension. The given filename does not have the %s extension.',
                    implode(' or ', $this->extension)
                )
            );
        }

        return true;
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->getFilename());
    }

    /**
     * @return mixed
     */
    public function openFile(): mixed
    {
        return $this->getFileContent();
    }

    /**
     * @param string $occurrence
     * @param bool $caseSensitive
     * @return int|null
     */
    public function firstOccurrence(string $occurrence, bool $caseSensitive = false): ?int
    {
        if ($caseSensitive === false) {
            $occurrence = strtolower($occurrence);
        }

        $file = new SplFileObject($this->getFilename());

        foreach ($file as $line) {
            if (str_contains($caseSensitive ? $line : strtolower($line), $occurrence) !== false) {
                $lineNumber = $file->key();
                $file = null;
                return $lineNumber;
            }
        }

        return null;
    }

    /**
     * @param string $occurrence
     * @param bool $caseSensitive
     * @return int|null
     */
    public function lastOccurrence(string $occurrence, bool $caseSensitive = false): ?int
    {
        if ($caseSensitive === false) {
            $occurrence = strtolower($occurrence);
        }

        $file = new SplFileObject($this->getFilename());

        $lineNumber = null;
        foreach ($file as $line) {
            if (str_contains($caseSensitive ? $line : strtolower($line), $occurrence) !== false) {
                $lineNumber = $file->key();
            }
        }

        $file = null;

        return $lineNumber;
    }

    /**
     * Get the line number $line of this file
     * @param int $lineNumber
     * @return string|null
     */
    public function getLine(int $lineNumber): ?string
    {
        $file = new SplFileObject($this->getFilename());

        foreach ($file as $line) {
            if ($file->key() === $lineNumber) {
                $file = null;
                return trim($line);
            }
        }

        return null;
    }

    /**
     * @param mixed $content
     * @param bool $recursive
     * @return bool
     */
    public function saveFile(mixed $content, bool $recursive = false): bool
    {
        return $this->filePutContents($content, $recursive);
    }

    /**
     * @param FileInterface $destinationFormat
     * @return bool
     */
    public function convertFile(FileInterface $destinationFormat): bool
    {
        $content = $this->getFileContent();

        return $destinationFormat->filePutContents($content);
    }

    /**
     * @return string|array
     */
    public function getExtension(): string|array
    {
        return $this->extension;
    }

    /**
     * @return bool|int
     */
    public function countLines(): bool|int
    {
        $file = new SplFileObject($this->getFilename());

        $file->seek(PHP_INT_MAX);
        $lineNumber = $file->key();

        $file = null;

        return $lineNumber + 1;
    }

    /**
     * @param int $line
     * @param string $content
     */
    public function insertContent(int $line, string $content): void
    {
        //create new tmp file
        $tmpFilename = dirname($this->getFilename()) . DIRECTORY_SEPARATOR . 'tmp.MulerTech';
        $tmpFile = new SplFileObject($tmpFilename, 'w+b');
        $file = new SplFileObject($this->getFilename(), 'r+');

        // Copy the file until the line on the tmp file
        while (!$file->eof()) {
            $tmpFile->fwrite($file->fgets());
            if ($file->key() === $line) {
                break;
            }
        }

        // Prepare the content to insert
        $newContent = $this->prepareFileContent($content);

        //copy this content on the tmp file
        foreach ($newContent as $newLine) {
            $tmpFile->fwrite($newLine . PHP_EOL);
        }

        // Copy the rest of the file
        while (!$file->eof()) {
            $tmpFile->fwrite($file->fgets());
        }

        //copy the entire tmp file on the file
        $file->ftruncate(0);
        $file->rewind();
        $tmpFile->rewind();
        while (!$tmpFile->eof()) {
            $file->fwrite($tmpFile->fgets());
        }
        $file = null;
        $tmpFile = null;

        //delete the tmp file if success
        unlink($tmpFilename);
    }

    /**
     * @param mixed $content
     * @param bool $recursive
     * @return bool
     */
    protected function filePutContents(mixed $content, bool $recursive = false): bool
    {
        $filename = $this->getFilename();

        if (file_exists($filename) && !is_writable($filename)) {
            throw new RuntimeException(
                sprintf('Unable to save the file "%s", it is write protected.', $filename)
            );
        }

        $parent = dirname($filename);

        if (!is_dir($parent)) {
            if (!$recursive) {
                throw new RuntimeException(
                    sprintf(
                        'Unable to save the file "%s", the parent folder "%s" does not exist.',
                        $filename,
                        $parent
                    )
                );
            }

            PathManipulation::folderCreate($parent, 0777, true);
        }

        return file_put_contents($filename, $content);
    }

    /**
     * @return string
     */
    protected function getFileContent(): string
    {
        if (false === $content = file_get_contents($this->getFilename())) {
            throw new RuntimeException(
                sprintf('Unable to read the content of file "%s".', $this->getFilename())
            );
        }

        return $content;
    }

    /**
     * @return string
     */
    protected function getFilename(): string
    {
        if (!isset($this->filename)) {
            throw new RuntimeException(
                'Class FileManipulation, function checkFilename. The filename is not defined.'
            );
        }

        return $this->filename;
    }

    /**
     * @param string $start
     * @return string|null
     */
    protected function findLineStartsWith(string $start): ?string
    {
        $handle = fopen($this->getFilename(), 'rb');

        while (!feof($handle)) {
            $buffer = fgets($handle, 4096);

            if ($buffer === false) {
                break;
            }

            if (str_starts_with($buffer, $start)) {
                fclose($handle);
                return trim($buffer);
            }
        }

        return null;
    }

    /**
     * @param string $contain
     * @param bool $caseSensitive
     * @return string|null
     */
    protected function findLineContains(string $contain, bool $caseSensitive = false): ?string
    {
        if ($caseSensitive === false) {
            $contain = strtolower($contain);
        }

        $handle = fopen($this->getFilename(), 'rb');

        while (!feof($handle)) {
            $buffer = fgets($handle, 4096);

            if ($buffer === false) {
                break;
            }

            if (str_contains($caseSensitive ? $buffer : strtolower($buffer), $contain) !== false) {
                fclose($handle);
                return trim($buffer);
            }
        }

        return null;
    }

    /**
     * @param string $content
     * @return string[]
     */
    private function prepareFileContent(string $content): array
    {
        if ($content === PHP_EOL) {
            return [''];
        }

        if (!str_contains($content, PHP_EOL)) {
            return [$content];
        }

        $arrayContent = explode(PHP_EOL, $content);

        if (empty(end($arrayContent))) {
            array_pop($arrayContent);
        }

        return $arrayContent;
    }
}
