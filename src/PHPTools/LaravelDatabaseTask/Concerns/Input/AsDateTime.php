<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Illuminate\Support\Traits\Conditionable;
use PHPTools\LaravelDatabaseTask\Enums\InputType;

trait AsDateTime
{
    use Conditionable;
    use HasNaming;
    use HasType;
    use HasValidation;
    use HasValue;

    protected string | \Closure $dateDisplayFormat = 'Y-m-d';

    protected string | \Closure $datetimeDisplayFormat = 'Y-m-d H:i:s';

    protected bool | \Closure $hasTime = false;

    public function asDateTime(bool | \Closure $hasTime = true): static
    {
        $this->hasTime = $hasTime;

        return $this->setType(InputType::DATETIME);
    }

    public function hasTime(): bool
    {
        return (bool) $this->evaluate($this->hasTime);
    }

    public function dateDisplayFormat(string | \Closure $format): static
    {
        $this->dateDisplayFormat = $format;

        return $this;
    }

    public function datetimeDisplayFormat(string | \Closure $format): static
    {
        $this->datetimeDisplayFormat = $format;

        return $this;
    }

    public function getDisplayFormat(): string
    {
        return $this->hasTime()
            ? (string) $this->evaluate($this->datetimeDisplayFormat)
            : (string) $this->evaluate($this->dateDisplayFormat);
    }
}
