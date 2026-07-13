<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Output;

trait AsFileOutput
{
    use HasExpires;
    use HasValue {
        getValue as protected hasValueGetValue;
    }

    public function getValue(): ?\SplFileObject
    {
        $value = $this->hasValueGetValue();

        if ($value instanceof \SplFileObject) {
            return $value;
        }

        return null;
    }
}
