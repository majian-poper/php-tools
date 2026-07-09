<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Output;

trait AsTextOutput
{
    use HasExpires;
    use HasValue {
        getValue as protected hasValueGetValue;
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
