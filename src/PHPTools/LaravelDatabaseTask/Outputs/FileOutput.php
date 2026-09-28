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
        $value = $this->baseGetValue();

        // 1. 设置了 value, 且 value 是 \SplFileObject 实例，则直接返回
        if ($value instanceof \SplFileObject) {
            return $value;
        }

        if (! isset($this->file)) {
            return null;
        }

        return $this->file->getSize() > 0
            ? $this->file // 2. file 含有内容 (task 操作 output 写入内容), 直接返回
            : $this->writeStream($this->file); // 3. file 为空, 则尝试写入 stream 到 file

    }
}
