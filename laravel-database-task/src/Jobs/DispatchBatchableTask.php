<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Models;

class DispatchBatchableTask implements ShouldQueue
{
    use Concerns\WithBatchableTask;
    use Queueable;

    public function __construct(Models\DatabaseTask $databaseTask)
    {
        $this->setDatabaseTask($databaseTask);
    }

    public function displayName(): string
    {
        return \sprintf('%s.dispatch-batch', $this->databaseTask->job_name);
    }

    public function handle(): void
    {
        $databaseTask = $this->getDatabaseTask();

        Events\BatchableTaskDispatching::dispatch($databaseTask);

        try {
            $task = $this->getBatchableTask();

            $batchableInputs = collect($task->getBatchableInputs(...$databaseTask->getNonBatchableInputs()))
                ->whereInstanceOf(Contracts\BatchableInput::class);

            $batchableInputValues = $batchableInputs->map(
                static fn(Contracts\BatchableInput $input): array => DatabaseTaskFacade::fromInput($input, $databaseTask)->getAttributes()
            );

            $jobs = $batchableInputs
                ->map(static fn(Contracts\BatchableInput $batchInput): int => $batchInput->getBatchOrder())
                ->unique()
                ->map(static fn(int $batchOrder): RunBatchableTask => (new RunBatchableTask($databaseTask, $batchOrder)));

            if ($jobs->isEmpty()) {
                throw new \RuntimeException(__('database-task::tasks.errors.no_data'));
            }

            $databaseTask->getConnection()->transaction(
                fn() => $this->saveBatchableInputs($databaseTask, $batchableInputValues)
            );

            $this->batch()->add($jobs);

            Events\BatchableTaskDispatchFinished::dispatch($databaseTask);
        } catch (\Throwable $e) {
            $this->markAsFailed($databaseTask, $e->getMessage());

            Events\BatchableTaskDispatchFailed::dispatch($databaseTask, $e);
        }
    }

    protected function saveBatchableInputs(Models\DatabaseTask $databaseTask, Collection $batchableInputValues): void
    {
        $databaseTask->outputs()->where('batch_order', '>', 0)->delete();
        $databaseTask->inputs()->where('batch_order', '>', 0)->delete();

        $batchableInputValues->chunk(100)->each(
            static fn(Collection $chunk) => $databaseTask->inputs()->insert($chunk->all())
        );
    }
}
