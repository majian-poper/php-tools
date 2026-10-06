<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class TextOutput implements Contracts\BatchableOutput
{
    use Concerns\HasValue {
        getValue as protected baseGetValue;
    }
    use Concerns\InteractsWithBatchable;
    use Concerns\Output\HasExpires;

    public function __construct(string $text = '')
    {
        if (filled($text)) {
            $this->value($text);
        }
    }

    public function getValue(): string
    {
        $value = $this->baseGetValue();

        if (\is_scalar($value) || $value instanceof \Stringable) {
            return (string) $value;
        }

        return '';
    }
}
