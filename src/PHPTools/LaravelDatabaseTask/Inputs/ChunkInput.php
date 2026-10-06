<?php

namespace PHPTools\LaravelDatabaseTask\Inputs;

use PHPTools\LaravelDatabaseTask\Concerns;
use PHPTools\LaravelDatabaseTask\Contracts;

class ChunkInput implements Contracts\BatchableInput
{
    use Concerns\Input\AsNumber;
    use Concerns\InteractsWithBatchable;
}
