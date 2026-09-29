<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

use Filament\Support\Concerns\EvaluatesClosures;

/**
 * @mixin \SplFileObject
 */
trait HasFileObject
{
    use EvaluatesClosures;

    protected ?\SplFileObject $file = null;

    /** @var null | resource | \Closure */
    protected $stream = null;

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

    /**
     * @param resource | \Closure $stream
     */
    public function stream($stream = null): static
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * @return null | resource
     */
    public function getStream()
    {
        $stream = $this->evaluate($this->stream);

        if (\is_resource($stream)) {
            return $stream;
        }

        return null;
    }

    protected function writeStream(\SplFileObject $to): ?\SplFileObject
    {
        if (! ($to->isWritable() || $to instanceof \SplTempFileObject)) {
            return null;
        }

        $from = $this->getStream();

        if (! \is_resource($from)) {
            return $to;
        }

        try {
            while (! \feof($from)) {
                $to->fwrite(\fread($from, 8192));
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error writing stream: ' . $e->getMessage(), previous: $e);
        } finally {
            \fclose($from);
        }

        $to->rewind();

        return $to;
    }
}
