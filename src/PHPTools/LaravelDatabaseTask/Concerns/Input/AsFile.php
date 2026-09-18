<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Illuminate\Support\Traits\Conditionable;
use PHPTools\LaravelDatabaseTask\Enums\InputType;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;

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

    public function isFile(): bool
    {
        return DatabaseTaskFacade::valueIsFile($this->getValue());
    }
}
