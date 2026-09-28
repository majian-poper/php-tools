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

class DispatchProcessJobCommand extends Command
{
    protected $signature = 'task:dispatch-process-job';

    protected $description = 'Dispatch process database task jobs.';

    public function handle()
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = DatabaseTaskFacade::resolveModel(Models\DatabaseTask::class)
            ->newQuery()
            ->whereHas('inputs')
            ->where('status', TaskStatus::APPROVED)
            ->where(static fn(Builder $query) => $query->whereNull('schedules_at')->orWhere('schedules_at', '<=', now()))
            ->orderBy('id');

        if (filled($databaseTask = (clone $query)->first())) {
            $this->dispatchJob($databaseTask);
        }

        if ((clone $query)->exists()) {
            Artisan::queue('task:dispatch-process-job')->delay(config('database-task.queue.delay'));
        }
    }

    protected function dispatchJob(Models\DatabaseTask $databaseTask): void
    {
        $task = $databaseTask->toTask();

        if (! $task instanceof Contracts\TaskInterface) {
            $this->dispatchFailed($databaseTask, __('database-task::tasks.errors.task_class_not_found'));

            return;
        }

        if (! $databaseTask->moveToStatus(to: TaskStatus::PROCESSING, from: TaskStatus::APPROVED)) {
            $this->dispatchFailed($databaseTask, __('database-task::tasks.errors.task_status_update_failed'));

            return;
        }

        $databaseTask->outputs()->delete();

        Bus::batch([])
            ->name(\sprintf('Process %s#%d', class_basename($task), $databaseTask->id))
            ->add($this->jobsFor($databaseTask))
            ->then($this->thenFor($databaseTask))
            ->dispatch();
    }

    protected function jobsFor(Models\DatabaseTask $databaseTask): array
    {
        /** @var \Illuminate\Support\Collection<int> $batchOrders */
        $batchOrders = $databaseTask->inputs()->distinct()->pluck('batch_order');

        return $batchOrders
            ->when($batchOrders->max() > 0)->reject(0) // 当有非 0 批次任务时，排除批次号为 0 的任务
            ->map(static fn(int $batchOrder) => new Jobs\ProcessTask($databaseTask, $batchOrder))
            ->all();
    }

    protected function thenFor(Models\DatabaseTask $databaseTask): \Closure
    {
        return $databaseTask->toTask() instanceof Contracts\BatchableTaskInterface
            ? static fn() => Jobs\MergeTask::dispatch($databaseTask)->delay(config('database-task.queue.delay'))
            : static fn() => null;
    }

    protected function dispatchFailed(Models\DatabaseTask $databaseTask, string $reason): void
    {
        $databaseTask->moveToFailedStatus($reason);

        Events\TaskProcessFailed::dispatch($databaseTask, new \RuntimeException($reason));
    }
}
