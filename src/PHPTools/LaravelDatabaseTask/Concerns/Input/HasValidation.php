<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Filament\Support\Concerns\EvaluatesClosures;

trait HasValidation
{
    use EvaluatesClosures;

    protected bool | \Closure $required = false;

    protected array | \Closure $requiredWithoutAll = [];

    protected array | \Closure $extraInputAttributes = [];

    public function required(bool | \Closure $condition = true): self
    {
        $this->required = $condition;

        return $this;
    }

    public function isRequired(): bool
    {
        return (bool) $this->evaluate($this->required);
    }

    public function requiredWithoutAll(array | \Closure $fields): self
    {
        $this->requiredWithoutAll = $fields;

        return $this;
    }

    public function requiredWithoutAllFields(): array
    {
        return (array) $this->evaluate($this->requiredWithoutAll);
    }

    public function extraInputAttributes(array | \Closure $extraInputAttributes): self
    {
        $this->extraInputAttributes = $extraInputAttributes;

        return $this;
    }

    public function getExtraInputAttributes(): array
    {
        return (array) $this->evaluate($this->extraInputAttributes);
    }
}
