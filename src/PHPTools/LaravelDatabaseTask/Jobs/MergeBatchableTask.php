<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Models;

class MergeBatchableTask implements ShouldQueue
{
    use Concerns\WithTask;
    use Queueable;

    public function __construct(Models\DatabaseTask $databaseTask)
    {
        $this->setDatabaseTask($databaseTask);
    }

    public function displayName(): string
    {
        return \sprintf('%s.merge', $this->databaseTask->job_name);
    }

    public function handle(): void
    {
        $databaseTask = $this->getDatabaseTask();

        Events\BatchableTaskMerging::dispatch($databaseTask);

        try {
            $task = $this->getTask();

            if (! $task instanceof Contracts\BatchableTaskInterface) {
                throw new \RuntimeException(__('database-task::tasks.errors.task_not_batchable'));
            }

            $mergedOutput = $task->mergeBatchableOutputs(...$databaseTask->getBatchableOutputs());

            if ($mergedOutput instanceof Contracts\BatchableOutput && $mergedOutput->getBatchOrder() !== 0) {
                throw new \RuntimeException(__('database-task::tasks.errors.output_should_not_be_batchable'));
            }

            $databaseTask->moveToProcessedStatus($mergedOutput);

            Events\BatchableTaskMergeFinished::dispatch($databaseTask);
        } catch (\Throwable $e) {
            $this->markAsFailed($databaseTask, $e->getMessage());

            Events\BatchableTaskMergeFailed::dispatch($databaseTask, $e);
        }
    }
}
