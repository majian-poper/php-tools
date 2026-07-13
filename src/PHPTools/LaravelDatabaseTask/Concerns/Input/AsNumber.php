<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use PHPTools\LaravelDatabaseTask\Enums\InputType;

trait AsNumber
{
    use AsFile;

    public function asNumber(): static
    {
        return $this->setType(InputType::NUMBER);
    }
}
