<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums;
use PHPTools\LaravelDatabaseTask\Events;

class MergeTask extends BatchJob implements ShouldQueue
{
    public function handle(): void
    {
        Events\TaskMerging::dispatch($this->databaseTask);

        try {
            $task = $this->getTask();

            if (! $task instanceof Contracts\BatchableTask) {
                throw new \RuntimeException(__('database-task::tasks.errors.task_not_batchable'));
            }

            $outputs = $this->databaseTask->outputs()
                ->where('batch_order', '!=', 0)
                ->get()
                ->map->toOutput()
                ->whereInstanceOf(Contracts\BatchableOutput::class);

            $mergedOutput = $task->mergeBatchableOutputs(...$outputs->all());

            if ($mergedOutput instanceof Contracts\BatchableOutput && $mergedOutput->getBatchOrder() !== 0) {
                throw new \RuntimeException(__('database-task::tasks.errors.output_should_not_be_batchable'));
            }

            $this->databaseTask->moveToProcessedStatus($mergedOutput);

            Events\TaskMerged::dispatch($this->databaseTask);
        } catch (\Throwable $e) {
            $this->databaseTask->moveToFailedStatus($e->getMessage());

            Events\TaskMergeFailed::dispatch($this->databaseTask, $e);
        }
    }

    protected function shouldSkip(): bool
    {
        return parent::shouldSkip() && $this->databaseTask->status !== Enums\TaskStatus::PROCESSING;
    }
}
