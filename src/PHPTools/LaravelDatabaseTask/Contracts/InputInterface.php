<?php

namespace PHPTools\LaravelDatabaseTask\Contracts;

use PHPTools\LaravelDatabaseTask\Enums\InputType;

interface InputInterface
{
    public function getType(): InputType;

    public function getLabel(): string;

    public function getName(): string;

    public function getValue(): mixed;

    public function isExcluded(): bool;
}
