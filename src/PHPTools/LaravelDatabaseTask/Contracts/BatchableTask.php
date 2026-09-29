<?php

namespace PHPTools\LaravelDatabaseTask\Contracts;

interface BatchableTask extends TaskInterface
{
    /**
     * @return iterable<BatchableInput>
     */
    public function getBatchableInputs(InputInterface ...$inputs): iterable;

    public function mergeBatchableOutputs(BatchableOutput ...$outputs): OutputInterface;
}
