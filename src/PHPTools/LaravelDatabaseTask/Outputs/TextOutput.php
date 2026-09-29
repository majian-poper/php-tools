<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class TextOutput implements Contracts\BatchableOutput
{
    use Concerns\InteractsWithBatchable;
    use Concerns\Output\HasExpires;
    use Concerns\Output\HasValue {
        getValue as protected hasValueGetValue;
    }

    public function __construct(string $text = '')
    {
        $this->value($text);
    }

    public function getValue(): string
    {
        $value = $this->hasValueGetValue();

        if (\is_string($value)) {
            return $value;
        }

        return '';
    }
}
