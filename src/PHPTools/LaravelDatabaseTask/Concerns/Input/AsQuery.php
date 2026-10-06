<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Illuminate\Support\Traits\Conditionable;
use PHPTools\LaravelDatabaseTask\Concerns\HasValue;
use PHPTools\LaravelDatabaseTask\Enums\InputType;

trait AsQuery
{
    use Conditionable;
    use HasNaming;
    use HasType;
    use HasValidation;
    use HasValue;

    public function getType(): InputType
    {
        return InputType::QUERY;
    }
}
