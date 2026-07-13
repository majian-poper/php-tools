<?php

namespace PHPTools\LaravelDatabaseTask\Contracts;

use Illuminate\Contracts\Support\Htmlable;

interface TaskInterface
{
    public static function getSupportInputs(): array;

    public function getTitle(): string;

    public function preview(InputInterface ...$inputs): Htmlable;

    public function run(InputInterface ...$inputs): OutputInterface;
}
