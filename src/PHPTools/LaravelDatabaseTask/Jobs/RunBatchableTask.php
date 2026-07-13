<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Models;

class RunBatchableTask implements ShouldQueue
{
    use Concerns\WithBatchableTask;
    use Queueable;

    public function __construct(Models\DatabaseTask $databaseTask, public int $batchOrder = 0)
    {
        $this->setDatabaseTask($databaseTask);
    }

    public function displayName(): string
    {
        return \sprintf('%s.%d.run', $this->databaseTask->job_name, $this->batchOrder);
    }

    public function handle(): void
    {
        $databaseTask = $this->getDatabaseTask();

        Events\BatchableTaskRunning::dispatch($databaseTask, $this->batchOrder);

        try {
            $task = $this->getBatchableTask();

            $output = $task->run(...$databaseTask->getInputsForBatch($this->batchOrder));

            if (! $output instanceof Contracts\BatchableOutput) {
                throw new \RuntimeException(__('database-task::tasks.errors.output_not_batchable'));
            }

            if ($output->getBatchOrder() !== $this->batchOrder) {
                throw new \RuntimeException(__('database-task::tasks.errors.output_batch_order_mismatch'));
            }

            $databaseTask->saveOutput($output);

            Events\BatchableTaskRunFinished::dispatch($databaseTask, $this->batchOrder);
        } catch (\Throwable $e) {
            $this->markAsFailed($databaseTask, $e->getMessage());

            Events\BatchableTaskRunFailed::dispatch($databaseTask, $this->batchOrder, $e);
        }
    }
}
