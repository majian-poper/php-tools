<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class BatchableTextOutput extends TextOutput implements Contracts\BatchableOutput
{
    use Concerns\InteractsWithBatchable;
}
