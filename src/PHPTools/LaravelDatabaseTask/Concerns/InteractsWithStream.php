<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

use Filament\Support\Concerns\EvaluatesClosures;

trait InteractsWithStream
{
    use EvaluatesClosures;

    /** @var null | resource | \Closure */
    protected $stream = null;

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
