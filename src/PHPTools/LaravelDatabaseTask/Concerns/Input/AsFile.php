<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Illuminate\Support\Traits\Conditionable;
use PHPTools\LaravelDatabaseTask\Enums\InputType;

trait AsFile
{
    use Conditionable;
    use HasNaming;
    use HasType;
    use HasValidation;
    use HasValue;

    protected bool | \Closure $canBeFile = false;

    public function asFile(): static
    {
        return $this->setType(InputType::FILE);
    }

    public function canBeFile(bool | \Closure $condition = true): static
    {
        $this->canBeFile = $condition;

        return $this;
    }

    public function isCanBeFile(): bool
    {
        if ($this->type === InputType::FILE) {
            return true;
        }

        return $this->type->canBeFile() && (bool) $this->evaluate($this->canBeFile);
    }

    public function isFile(): bool
    {
        return $this->type->canBeFile() && $this->getValue() instanceof \SplFileObject;
    }
}
