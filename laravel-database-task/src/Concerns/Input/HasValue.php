<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Filament\Support\Concerns\EvaluatesClosures;

trait HasValue
{
    use EvaluatesClosures;

    protected mixed $value;

    /**
     * asBoolean     => bool
     * asNumber      => int / iterable<int> / \SplFileObject
     * asQuery       => string
     * asDatetime    => \DateTimeInterface
     * asFile        => \SplFileObject
     * asSelect      => iterable<int | string>
     *
     * @return null | bool | int | string | \DateTimeInterface | \SplFileObject | iterable
     */
    public function getValue(): mixed
    {
        return $this->evaluate($this->value);
    }

    public function value(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }
}
