<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class FileOutput extends \SplFileObject implements Contracts\OutputInterface
{
    use Concerns\InteractsWithStream;
    use Concerns\Output\AsFileOutput {
        getValue as protected baseGetValue;
    }

    protected bool $autoDelete = true;

    public function __destruct()
    {
        if ($this->autoDelete && $this->isWritable()) {
            @\unlink($this->getRealPath());
        }
    }

    public function getValue(): ?\SplFileObject
    {
        $this->writeStream($this);

        return $this->baseGetValue() ?? $this;
    }

    public function autoClean(bool $autoClean = true): static
    {
        $this->autoDelete = $autoClean;

        return $this;
    }
}
