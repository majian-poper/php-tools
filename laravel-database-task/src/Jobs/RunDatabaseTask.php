<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\Skip;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Models;

class RunDatabaseTask implements ShouldQueue
{
    use Concerns\WithTask;
    use Queueable;

    public function __construct(Models\DatabaseTask $databaseTask)
    {
        $this->setDatabaseTask($databaseTask);
    }

    public function displayName(): string
    {
        return \sprintf('%s.run', $this->databaseTask->job_name);
    }

    public function middleware(): array
    {
        return [Skip::unless($this->isProcessing())];
    }

    public function handle(): void
    {
        $databaseTask = $this->getDatabaseTask();

        Events\TaskRunning::dispatch($databaseTask);

        try {
            $task = $this->getTask();

            $output = $task->run(...$databaseTask->getNonBatchableInputs());

            if ($output instanceof Contracts\BatchableOutput && $output->getBatchOrder() !== 0) {
                throw new \RuntimeException(__('database-task::tasks.errors.output_should_not_be_batchable'));
            }

            $databaseTask->moveToProcessedStatus($output);

            Events\TaskRunFinished::dispatch($databaseTask);
        } catch (\Throwable $e) {
            $this->markAsFailed($databaseTask, $e->getMessage());

            Events\TaskRunFailed::dispatch($databaseTask, $e);
        }
    }
}
