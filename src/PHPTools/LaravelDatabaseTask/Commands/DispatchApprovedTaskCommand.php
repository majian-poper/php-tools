<?php

namespace PHPTools\LaravelDatabaseTask\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums\TaskStatus;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Jobs;
use PHPTools\LaravelDatabaseTask\Models;

class DispatchApprovedTaskCommand extends Command
{
    protected $signature = 'approved-task:dispatch';

    protected $description = 'Dispatch approved database task jobs.';

    public function handle()
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = DatabaseTaskFacade::resolveModel(Models\DatabaseTask::class)
            ->newQuery()
            ->where('status', TaskStatus::APPROVED)
            ->where(
                static fn(Builder $query) => $query
                    ->whereNull('schedules_at')
                    ->orWhere('schedules_at', '<=', now())
            )
            ->orderBy('id');

        if (filled($databaseTask = (clone $query)->first())) {
            $this->dispatchJob($databaseTask);
        }

        if ((clone $query)->exists()) {
            Artisan::queue('approved-task:dispatch')->delay(5);
        }
    }

    protected function dispatchJob(Models\DatabaseTask $databaseTask): void
    {
        if (blank($task = $databaseTask->toTask())) {
            $this->dispatchFailed($databaseTask, __('database-task::tasks.errors.task_class_not_found'));

            return;
        }

        if (! $databaseTask->moveToStatus(to: TaskStatus::PROCESSING, from: TaskStatus::APPROVED)) {
            $this->dispatchFailed($databaseTask, __('database-task::tasks.errors.task_status_update_failed'));

            return;
        }

        Events\TaskDispatching::dispatch($databaseTask);

        if ($task instanceof Contracts\BatchableTaskInterface) {
            $this->dispatchBatchable($databaseTask);
        } else {
            $this->dispatchNonBatchable($databaseTask);
        }

        Events\TaskDispatchFinished::dispatch($databaseTask);
    }

    protected function dispatchBatchable(Models\DatabaseTask $databaseTask): void
    {
        Bus::batch(new Jobs\DispatchBatchableTask($databaseTask))
            ->name($databaseTask->job_name)
            ->then(static fn() => Jobs\MergeBatchableTask::dispatch($databaseTask)->delay(5))
            ->dispatch();
    }

    protected function dispatchNonBatchable(Models\DatabaseTask $databaseTask): void
    {
        Jobs\RunDatabaseTask::dispatch($databaseTask);
    }

    protected function dispatchFailed(Models\DatabaseTask $databaseTask, string $reason): void
    {
        $databaseTask->moveToFailedStatus($reason);

        Events\TaskDispatchFailed::dispatch($databaseTask, new \RuntimeException($reason));
    }
}
