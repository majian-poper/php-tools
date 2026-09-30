<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * @mixin \SplFileObject
 */
trait HasFileValue
{
    use HasValue {
        getValue as protected baseGetValue;
    }

    protected ?\SplFileObject $file = null;

    protected bool $autoClean = true;

    public function __call(string $name, array $arguments)
    {
        $file = $this->getValue();

        if ($file instanceof \SplFileObject && \method_exists($file, $name)) {
            return $file->{$name}(...$arguments);
        }

        return null;
    }

    public function autoClean(bool $autoClean = true): static
    {
        $this->autoClean = $autoClean;

        return $this;
    }

    public function getValue(): ?\SplFileObject
    {
        if ($this->file instanceof \SplFileObject) {
            return $this->file;
        }

        $value = $this->baseGetValue();

        return $this->file = match (true) {
            $value instanceof \SplFileObject => $value,
            $value instanceof TemporaryUploadedFile => $this->createTempFile($value->readStream()),
            \is_resource($value) => $this->createTempFile($value),
            default => null,
        };
    }

    /**
     * @return resource | null
     */
    public function getStream()
    {
        $value = $this->file ?? $this->baseGetValue();

        if ($value instanceof \SplFileObject) {
            $this->file ??= $value;
        }

        if (\is_resource($value)) {
            $this->file = $value = $this->createTempFile($value);
        }

        return match (true) {
            $value instanceof \SplFileObject => \fopen($value->getRealPath(), 'r'),
            $value instanceof TemporaryUploadedFile => $value->readStream(),
            default => null,
        };
    }

    protected function isBlockingResource($stream = null): bool
    {
        if (! \is_resource($stream)) {
            return false;
        }

        $metadata = \stream_get_meta_data($stream);

        return ($metadata['blocked'] ?? true) === true;
    }

    protected function createTempFile(mixed $stream): \SplFileObject
    {
        try {
            if (! $this->isBlockingResource($stream)) {
                throw new \RuntimeException('Stream is not a blocking resource.');
            }

            $tempFilePath = $this->getTempFilePath();

            $tempFile = \fopen($tempFilePath, 'w+');

            if ($tempFile === false) {
                throw new \RuntimeException('Failed to create temporary file.');
            }

            if (\stream_copy_to_stream($stream, $tempFile) === false) {
                throw new \RuntimeException('Failed to copy stream to temporary file.');
            }

            return $this->createFileObject($tempFilePath);
        } catch (\Throwable $e) {
            if (isset($tempFilePath)) {
                \unlink($tempFilePath);
            }

            throw new \RuntimeException('Failed to create temporary file from stream.', 0, $e);
        } finally {
            if (\is_resource($stream)) {
                \fclose($stream);
            }

            if (isset($tempFile) && \is_resource($tempFile)) {
                \fclose($tempFile);
            }
        }
    }

    protected function getTempFilePath(): string
    {
        $temp = \tempnam(\sys_get_temp_dir(), \sprintf('dbtask_%s_', Str::snake(class_basename(static::class))));

        if ($temp === false) {
            throw new \RuntimeException('Failed to create temporary file.');
        }

        return $temp;
    }

    protected function createFileObject(string $filePath, string $mode = 'r'): \SplFileObject
    {
        return new \SplFileObject($filePath, $mode);
    }

    protected function deleteTempFile(): bool
    {
        if (! $this->autoClean) {
            return false;
        }

        if ($this->file instanceof \SplFileObject && $this->file->isWritable()) {
            \unlink($this->file->getRealPath());
        }

        unset($this->file);

        return true;
    }
}
