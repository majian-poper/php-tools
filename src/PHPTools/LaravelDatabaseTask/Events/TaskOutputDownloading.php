<?php

namespace PHPTools\LaravelDatabaseTask\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTaskOutput;

class TaskOutputDownloading
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly DatabaseTaskOutput $output, public readonly ?Authenticatable $user)
    {
        //
    }
}
