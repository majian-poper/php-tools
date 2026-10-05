<?php

namespace PHPTools\LaravelDatabaseTask\Contracts;

interface DelayableTask
{
    public function delay(int $batchOrder = 0): int;
}
