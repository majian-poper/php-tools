<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Output;

use Filament\Support\Concerns\EvaluatesClosures;

trait HasValue
{
    use EvaluatesClosures;

    protected null | string | \Closure $value = null;

    /**
     * @return null | string | \SplFileObject
     */
    public function getValue(): null | string | \SplFileObject
    {
        return $this->evaluate($this->value);
    }

    public function value(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }
}
