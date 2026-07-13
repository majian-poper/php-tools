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

    /**
     * @param \SplFileObject $to
     *
     * @return \SplFileObject
     *
     * @throws \RuntimeException
     */
    protected function writeStream(\SplFileObject $to): \SplFileObject
    {
        $from = $this->getStream();

        $this->stream();

        if (! \is_resource($from)) {
            return $to;
        }

        if (! $to->isWritable() && ! ($to instanceof \SplTempFileObject)) {
            throw new \RuntimeException(\sprintf('Target [%s] is not writable.', \get_class($to)));
        }

        while (! \feof($from)) {
            $to->fwrite(\fread($from, 8192));
        }

        \fclose($from);

        $to->rewind();

        return $to;
    }
}
