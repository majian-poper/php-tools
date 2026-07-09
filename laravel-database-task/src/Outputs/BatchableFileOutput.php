<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class BatchableFileOutput extends FileOutput implements Contracts\BatchableOutput
{
    use Concerns\InteractsWithBatchable;
}
