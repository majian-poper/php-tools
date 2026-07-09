<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class TextOutput implements Contracts\OutputInterface
{
    use Concerns\Output\AsTextOutput;

    public function __construct(string $text = '')
    {
        $this->value($text);
    }
}
