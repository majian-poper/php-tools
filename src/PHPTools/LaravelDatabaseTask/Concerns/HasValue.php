<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

use Filament\Support\Concerns\EvaluatesClosures;

trait HasValue
{
    use EvaluatesClosures;

    protected mixed $value = null;

    /**
     * Input :
     * asBoolean     => bool
     * asDatetime    => \DateTimeInterface
     * asFile        => \SplFileObject
     * asNumber      => int / iterable<int>
     * asQuery       => string
     * asSelect      => iterable<int | string>
     *
     * Ouput :
     * FileOuput     => \SplFileObject
     * TextOutput    => string
     *
     * @return null | bool | int | string | iterable | \DateTimeInterface | \SplFileObject
     */
    public function getValue(): mixed
    {
        return $this->evaluate($this->value);
    }

    public function value(mixed $value): static
    {
        if (isset($this->value)) {
            throw new \RuntimeException('Value has already been set.');
        }

        $this->value = $value;

        return $this;
    }
}
