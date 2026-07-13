<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Input;

use Illuminate\Support\Str;

trait HasNaming
{
    public function getName(): string
    {
        return Str::of(static::class)->afterLast('\\')->snake()->toString();
    }

    public function getLabel(): string
    {
        $inputName = Str::of(static::class)->afterLast('\\')->snake();

        return __("database-task::tasks.inputs.{$inputName}.label");
    }
}
