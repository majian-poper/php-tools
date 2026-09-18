<?php

namespace PHPTools\LaravelDatabaseTask\Inputs;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class FileInput implements Contracts\InputInterface
{
    use Concerns\HasFileObject;
    use Concerns\Input\AsFile {
        getValue as protected baseGetValue;
    }
    use Concerns\InteractsWithStream;

    public function __construct(?string $filename = null, string $mode = 'w+')
    {
        $this->asFile();

        $filename ??= \tempnam(\sys_get_temp_dir(), 'dbtask_input_');

        $this->createFile($filename, $mode);
    }

    public function __destruct()
    {
        $this->deleteFile();
    }

    public function getValue(): \SplFileObject | null
    {
        $value = $this->getRawValue();

        if ($value instanceof \SplFileObject) {
            return $value;
        }

        return isset($this->file) ? $this->writeStream($this->file) : null;
    }

    public function value(TemporaryUploadedFile | \SplFileObject | null $value): static
    {
        if ($value instanceof TemporaryUploadedFile) {
            $this->stream(static fn() => $value->readStream());
        }

        $this->value = $value;

        return $this;
    }

    public function getRawValue(): TemporaryUploadedFile | \SplFileObject | null
    {
        return $this->baseGetValue();
    }
}
