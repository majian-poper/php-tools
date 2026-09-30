<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Illuminate\Support\Traits\Conditionable;
use PHPTools\LaravelDatabaseTask\Concerns\HasValue;
use PHPTools\LaravelDatabaseTask\Enums\InputType;

trait AsSelect
{
    use Conditionable;
    use HasNaming;
    use HasType;
    use HasValidation;
    use HasValue;

    protected array | \Closure $options = [];

    public function getType(): InputType
    {
        return InputType::SELECT;
    }

    public function asSelect(array | \Closure $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions(): array
    {
        return (array) $this->evaluate($this->options);
    }
}
