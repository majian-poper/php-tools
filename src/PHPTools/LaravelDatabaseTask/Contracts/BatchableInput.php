<?php

namespace PHPTools\LaravelDatabaseTask\Contracts;

interface BatchableInput extends InputInterface
{
    public function getBatchOrder(): int;
}
