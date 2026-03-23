<?php

declare(strict_types=1);

namespace MulerTech\FileManipulation;

/**
 * Class FileManipulation.
 *
 * @author Sébastien Muler
 */
class FileManipulation implements FileInterface
{
    /**
     * @param string|array<int, string> $extension
     */
    public function __construct(
        private readonly string $filename,
        protected string|array $extension = '',
    ) {
    }

    public function checkExtension(): bool
    {
        $extension = pathinfo($this->filename, PATHINFO_EXTENSION);

        if (is_string($this->extension) && $extension !== $this->extension) {
            throw new \RuntimeException(sprintf('Class FileManipulation, function checkExtension. The given filename does not have the %s extension.', $this->extension));
        }

        if (is_array($this->extension) && !in_array($extension, $this->extension, true)) {
            throw new \RuntimeException(sprintf('Class FileManipulation, function checkExtension. The given filename does not have the %s extension.', implode(' or ', $this->extension)));
        }

        return true;
    }

    public function exists(): bool
    {
        return file_exists($this->getFilename());
    }

    public function openFile(): mixed
    {
        return $this->getFileContent();
    }

    public function firstOccurrence(string $occurrence, bool $caseSensitive = false): ?int
    {
        $file = new \SplFileObject($this->getFilename());

        foreach ($file as $lineNumber => $line) {
            if (is_string($line) && $this->lineContains($line, $occurrence, $caseSensitive)) {
                return (int) $lineNumber;
            }
        }

        return null;
    }

    public function lastOccurrence(string $occurrence, bool $caseSensitive = false): ?int
    {
        $file = new \SplFileObject($this->getFilename());
        $lastLine = null;

        foreach ($file as $lineNumber => $line) {
            if (is_string($line) && $this->lineContains($line, $occurrence, $caseSensitive)) {
                $lastLine = (int) $lineNumber;
            }
        }

        return $lastLine;
    }

    /**
     * Get specific line content from the file.
     *
     * @return string|array<int, mixed>|null
     */
    public function getLine(int $lineNumber): string|array|null
    {
        $file = new \SplFileObject($this->getFilename());

        foreach ($file as $key => $line) {
            if ($line && $key === $lineNumber) {
                return is_string($line) ? trim($line) : $line;
            }
        }

        return null;
    }

    public function saveFile(mixed $content, bool $recursive = false): bool
    {
        return $this->filePutContents($content, $recursive);
    }

    public function convertFile(FileInterface $destinationFormat): bool
    {
        $content = $this->getFileContent();

        return $destinationFormat->filePutContents($content);
    }

    /**
     * @return string|array<int, string>
     */
    public function getExtension(): string|array
    {
        return $this->extension;
    }

    public function countLines(): bool|int
    {
        $file = new \SplFileObject($this->getFilename());

        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();

        return $lastLine + 1;
    }

    public function insertContent(int $line, string $content): void
    {
        // create new tmp file
        $tmpFilename = dirname($this->getFilename()).DIRECTORY_SEPARATOR.'tmp.MulerTech';
        $tmpFile = new \SplFileObject($tmpFilename, 'w+b');
        $file = new \SplFileObject($this->getFilename(), 'r+');

        // Copy the file until the line on the tmp file
        while (!$file->eof()) {
            $tmpFile->fwrite($file->fgets());
            if ($file->key() === $line) {
                break;
            }
        }

        // Prepare the content to insert
        $newContent = $this->prepareFileContent($content);

        // copy this content on the tmp file
        foreach ($newContent as $newLine) {
            $tmpFile->fwrite($newLine.PHP_EOL);
        }

        // Copy the rest of the file
        while (!$file->eof()) {
            $tmpFile->fwrite($file->fgets());
        }

        // copy the entire tmp file on the file
        $file->ftruncate(0);
        $file->rewind();
        $tmpFile->rewind();
        while (!$tmpFile->eof()) {
            $file->fwrite($tmpFile->fgets());
        }
        $file = null;
        $tmpFile = null;

        // delete the tmp file if success
        unlink($tmpFilename);
    }

    /**
     * Find and return the first line that contains a given string.
     *
     * @param string $contain       the text to search
     * @param bool   $caseSensitive whether the search is case-sensitive
     */
    public function findLineContains(string $contain, bool $caseSensitive = false): ?string
    {
        if (!$this->exists()) {
            $this->fileDoesNotExists();
        }

        $file = new \SplFileObject($this->getFilename());

        foreach ($file as $line) {
            if (is_string($line) && $this->lineContains($line, $contain, $caseSensitive)) {
                return trim($line);
            }
        }

        return null;
    }

    public function filePutContents(mixed $content, bool $recursive = false): bool
    {
        $filename = $this->getFilename();

        if ($this->exists() && !is_writable($filename)) {
            throw new \RuntimeException(sprintf('Unable to save the file "%s", it is write protected.', $filename));
        }

        $parent = dirname($filename);

        if (!is_dir($parent)) {
            if (!$recursive) {
                throw new \RuntimeException(sprintf('Unable to save the file "%s", the parent folder "%s" does not exist.', $filename, $parent));
            }

            PathManipulation::folderCreate($parent, 0777, true);
        }

        $result = @file_put_contents($filename, $content);

        if (false === $result) {
            throw new \RuntimeException(sprintf('Unable to write the file "%s".', $filename));
        }

        return true;
    }

    protected function getFileContent(): ?string
    {
        if (!$this->exists()) {
            throw new \RuntimeException(sprintf('The file "%s" does not exist.', $this->getFilename()));
        }

        return file_get_contents($this->getFilename()) ?: null;
    }

    protected function getFilename(): string
    {
        return $this->filename;
    }

    protected function findLineStartsWith(string $start): ?string
    {
        if (!$this->exists()) {
            $this->fileDoesNotExists();
        }

        $file = new \SplFileObject($this->getFilename());

        while (!$file->eof()) {
            $line = $file->fgets();
            if (str_starts_with($line, $start)) {
                return trim($line);
            }
        }

        return null;
    }

    private function lineContains(string $line, string $contain, bool $caseSensitive = false): bool
    {
        $haystack = $caseSensitive ? $line : strtolower($line);
        $needle = $caseSensitive ? $contain : strtolower($contain);

        return str_contains($haystack, $needle);
    }

    private function fileDoesNotExists(): void
    {
        throw new \RuntimeException(sprintf('The file "%s" does not exist.', $this->getFilename()));
    }

    /**
     * @return string[]
     */
    private function prepareFileContent(string $content): array
    {
        // If the content is just a line break, return an empty string as a line.
        if (PHP_EOL === $content) {
            return [''];
        }
        // If the content does not contain any line breaks, return it as a single line.
        if (!str_contains($content, PHP_EOL)) {
            return [$content];
        }

        // Otherwise, split by PHP_EOL.
        return explode(PHP_EOL, $content);
    }
}
