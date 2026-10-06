<?php

namespace PHPTools\LaravelDatabaseTask\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTaskInput;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTaskOutput;

class TaskMediaDownloading
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly DatabaseTaskInput | DatabaseTaskOutput $output, public readonly ?Authenticatable $user)
    {
        //
    }
}
