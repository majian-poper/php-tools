<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

/**
 * @mixin \SplFileObject
 */
trait HasFileObject
{
    protected ?\SplFileObject $file = null;

    protected bool $shouldDelete = true;

    public function __call(string $name, array $arguments)
    {
        if (isset($this->file) && \method_exists($this->file, $name)) {
            return $this->file->{$name}(...$arguments);
        }

        return null;
    }

    public function shouldDelete(bool $shouldDelete): static
    {
        $this->shouldDelete = $shouldDelete;

        return $this;
    }

    public function createFile(string $filename, string $mode = 'r'): \SplFileObject
    {
        return $this->file = new \SplFileObject($filename, $mode);
    }

    public function deleteFile(): bool
    {
        if (! $this->shouldDelete) {
            return false;
        }

        if (! $this->file?->isWritable()) {
            return false;
        }

        \unlink($this->file->getRealPath());

        unset($this->file);

        return true;
    }
}
