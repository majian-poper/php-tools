<?php

namespace PHPTools\LaravelDatabaseTask\Inputs;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class FileInput implements Contracts\InputInterface
{
    use Concerns\Input\AsFile {
        value as protected asFileValue;
    }

    protected ?TemporaryUploadedFile $uploadedFile = null;

    public function __destruct()
    {
        unset($this->value, $this->uploadedFile);

        $this->deleteTempFile();
    }

    public function value(mixed $value): static
    {
        $this->asFileValue($value);

        if ($value instanceof TemporaryUploadedFile) {
            $this->uploadedFile = $value;
        }

        return $this;
    }

    public function getUploadedFile(): ?TemporaryUploadedFile
    {
        return $this->uploadedFile;
    }
}
