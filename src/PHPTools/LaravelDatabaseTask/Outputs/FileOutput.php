<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class FileOutput implements Contracts\OutputInterface
{
    use Concerns\HasFileObject;
    use Concerns\InteractsWithStream;
    use Concerns\Output\AsFileOutput {
        getValue as protected baseGetValue;
    }

    public function __construct(?string $filename = null, string $mode = 'w+')
    {
        $filename ??= \tempnam(\sys_get_temp_dir(), 'dbtask_output_');

        $this->createFile($filename, $mode);
    }

    public function __destruct()
    {
        $this->deleteFile();
    }

    public function getValue(): ?\SplFileObject
    {
        $value = isset($this->file) ? $this->writeStream($this->file) : null;

        return $value ?? $this->baseGetValue();
    }
}
