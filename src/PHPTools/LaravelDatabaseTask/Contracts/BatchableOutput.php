<?php

namespace PHPTools\LaravelDatabaseTask\Contracts;

interface BatchableOutput extends OutputInterface
{
    public function getBatchOrder(): int;
}
