<?php

namespace PHPTools\LaravelDatabaseTask\Jobs\Concerns;

use Illuminate\Bus\Batchable;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Models;

trait WithBatchableTask
{
    use Batchable;
    use WithTask {
        shouldSkip as baseShouldSkip;
        markAsFailed as baseMarkAsFailed;
    }

    public function getBatchableTask(): ?Contracts\BatchableTaskInterface
    {
        $task = $this->getTask();

        if (! $task instanceof Contracts\BatchableTaskInterface) {
            throw new \RuntimeException(__('database-task::tasks.errors.task_not_batchable'));
        }

        return $task;
    }

    protected function markAsFailed(Models\DatabaseTask $databaseTask, string $reason): void
    {
        $this->batch()->cancel();

        $this->baseMarkAsFailed($databaseTask, $reason);
    }

    protected function shouldSkip()
    {
        return ! ($this->batching() && $this->isProcessing());
    }
}
