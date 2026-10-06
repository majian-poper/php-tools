<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Illuminate\Support\Traits\Conditionable;
use PHPTools\LaravelDatabaseTask\Concerns\HasFileValue;
use PHPTools\LaravelDatabaseTask\Enums\InputType;

trait AsFile
{
    use Conditionable;
    use HasFileValue;
    use HasNaming;
    use HasType;
    use HasValidation;

    public function getType(): InputType
    {
        return InputType::FILE;
    }
}
