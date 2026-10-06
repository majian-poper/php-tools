<?php

namespace PHPTools\LaravelDatabaseTask\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums\TaskStatus;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Jobs;
use PHPTools\LaravelDatabaseTask\Models;

class DispatchValidateJobCommand extends Command
{
    protected $signature = 'task:dispatch-validate-job';

    protected $description = 'dispatch validate database task jobs.';

    public function handle(): int
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = DatabaseTaskFacade::resolveModel(Models\DatabaseTask::class)
            ->newQuery()
            ->whereHas('inputs')
            ->where('status', TaskStatus::CREATED)
            ->orderBy('id');

        if (filled($databaseTask = (clone $query)->first())) {
            Cache::lock("{$this->signature}:{$databaseTask->id}", config('database-task.queue.timeout'))->get(
                fn() => $this->dispatchJob($databaseTask)
            );
        }

        if ((clone $query)->exists()) {
            Artisan::queue('task:dispatch-validate-job')->delay(config('database-task.queue.delay'));
        }

        return 0;
    }

    protected function dispatchJob(Models\DatabaseTask $databaseTask): void
    {
        Events\TaskValidating::dispatch($databaseTask, 0);

        $task = $databaseTask->toTask();

        if (! $task instanceof Contracts\TaskInterface) {
            $this->dispatchFailed($databaseTask, __('database-task::tasks.errors.task_class_not_found'));

            return;
        }

        if (! $databaseTask->toValidating()) {
            $this->dispatchFailed($databaseTask, __('database-task::tasks.errors.task_status_update_failed'));

            return;
        }

        if (! $task instanceof Contracts\ShouldValidate) {
            $databaseTask->toValidated()
                ? Events\TaskValidated::dispatch($databaseTask, 0)
                : $this->dispatchFailed($databaseTask, __('database-task::tasks.errors.task_status_update_failed'));

            return;
        }

        $databaseTask->inputs()->update(['validated_at' => null]);

        Bus::batch([])
            ->name(\sprintf('Validate %s#%d', class_basename($task), $databaseTask->id))
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
            ->map(static fn(int $batchOrder) => new Jobs\ValidateTask($databaseTask, $batchOrder))
            ->all();
    }

    protected function thenFor(Models\DatabaseTask $databaseTask): \Closure
    {
        return static function () use ($databaseTask) {
            $success = $databaseTask->inputs()->whereNull('validated_at')->doesntExist()
                ? $databaseTask->toValidated()
                : false;

            if ($success) {
                Events\TaskValidated::dispatch($databaseTask, 0);
            } else {
                $databaseTask->toFailed($reason = __('database-task::tasks.task_validate_failed'));

                Events\TaskValidateFailed::dispatch($databaseTask, 0, new \RuntimeException($reason));
            }
        };
    }

    protected function dispatchFailed(Models\DatabaseTask $databaseTask, string $reason): void
    {
        $databaseTask->toFailed($reason);

        Events\TaskValidateFailed::dispatch($databaseTask, 0, new \RuntimeException($reason));
    }
}
