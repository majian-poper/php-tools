<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Illuminate\Support\Traits\Conditionable;
use PHPTools\LaravelDatabaseTask\Enums\InputType;

trait AsQuery
{
    use Conditionable;
    use HasNaming;
    use HasType;
    use HasValidation;
    use HasValue;

    public function asQuery(): static
    {
        return $this->setType(InputType::QUERY);
    }
}
