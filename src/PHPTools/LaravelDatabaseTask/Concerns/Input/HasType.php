<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Filament\Support\Concerns\EvaluatesClosures;
use PHPTools\LaravelDatabaseTask\Enums\InputType;

trait HasType
{
    use EvaluatesClosures;

    protected InputType $type = InputType::QUERY;

    protected bool | \Closure $isExcluded = false;

    protected bool | \Closure $canBeExcluded = false;

    protected bool | \Closure $canBeMultiple = false;

    public function getType(): InputType
    {
        return $this->type;
    }

    protected function setType(InputType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function excluded(bool | \Closure $condition = true): static
    {
        $this->isExcluded = $condition;

        return $this;
    }

    public function isExcluded(): bool
    {
        return $this->getType()->canBeExcluded() && (bool) $this->evaluate($this->isExcluded);
    }

    public function canBeExcluded(bool | \Closure $condition = true): static
    {
        $this->canBeExcluded = $condition;

        return $this;
    }

    public function isCanBeExcluded(): bool
    {
        return $this->getType()->canBeExcluded() && (bool) $this->evaluate($this->canBeExcluded);
    }

    public function multiple(bool | \Closure $condition = true): static
    {
        $this->canBeMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->getType()->canBeMultiple() && (bool) $this->evaluate($this->canBeMultiple);
    }

    // --- Localization driven by InputType ---

    public function getPlaceholder(): string
    {
        return $this->getType()->getPlaceholder($this->getLabel(), $this->isMultiple());
    }

    public function getHelperText(): string
    {
        return $this->getType()->getHelperText($this->getLabel(), $this->isMultiple());
    }
}
