<?php

namespace PHPTools\LaravelDatabaseTask\Events;

use Illuminate\Foundation\Events\Dispatchable;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTask;

class BatchableTaskRunFinished
{
    use Dispatchable;

    public function __construct(public readonly DatabaseTask $databaseTask, public readonly int $batchOrder)
    {
        //
    }
}
