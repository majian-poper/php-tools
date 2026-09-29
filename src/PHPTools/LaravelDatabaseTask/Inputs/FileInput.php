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

    public function getValue(): ?\SplFileObject
    {
        $value = $this->getRawValue();

        // 1. 设置了 value, 且 value 是 \SplFileObject 实例，则直接返回
        if ($value instanceof \SplFileObject) {
            return $value;
        }

        if (! isset($this->file)) {
            return null;
        }

        return $this->file->getSize() > 0
            ? $this->file // 2. file 含有内容 (来自其他操作向 input 写入过内容), 直接返回
            : $this->writeStream($this->file); // 3. file 为空, 则尝试写入 stream 到 file
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
