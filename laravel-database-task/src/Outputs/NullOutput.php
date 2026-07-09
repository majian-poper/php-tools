<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class NullOutput implements Contracts\BatchableOutput
{
    use Concerns\InteractsWithBatchable;

    public static function create(): static
    {
        return new static;
    }

    /**
     * Null output does not support a value, so this method always returns null.
     */
    public function getValue(): null
    {
        return null;
    }

    /**
     * Null output does not support expiration, so this method always returns null.
     */
    public function getExpiresAt(): null
    {
        return null;
    }
}
