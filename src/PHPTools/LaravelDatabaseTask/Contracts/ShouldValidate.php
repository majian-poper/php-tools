<?php

namespace PHPTools\LaravelDatabaseTask\Contracts;

interface ShouldValidate
{
    public function validate(InputInterface ...$inputs): bool;
}
