<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class FileOutput implements Contracts\BatchableOutput
{
    use Concerns\HasFileValue;
    use Concerns\InteractsWithBatchable;
    use Concerns\Output\HasExpires;

    public function __construct(?string $filename = null, ?string $mode = null)
    {
        if (filled($filename)) {
            $this->value($this->createFileObject($filename, $mode ?? 'w+'));
        }
    }

    public function __destruct()
    {
        unset($this->value);

        $this->deleteTempFile();
    }
}
