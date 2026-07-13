<?php

namespace PHPTools\LaravelDatabaseTask\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTask;

class TaskRequested
{
    use Dispatchable;

    public function __construct(public readonly DatabaseTask $databaseTask, public readonly ?Authenticatable $user)
    {
        //
    }
}
