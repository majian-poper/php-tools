<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums;
use PHPTools\LaravelDatabaseTask\Events;

class ValidateTask extends BatchJob implements ShouldQueue
{
    public function handle(): void
    {
        Events\TaskValidating::dispatch($this->databaseTask);

        try {
            $task = $this->getTask();

            if (! $task instanceof Contracts\ShouldValidate) {
                throw new \RuntimeException(__('database-task::tasks.errors.task_not_validatable'));
            }

            $inputModels = $this->databaseTask->inputs()
                ->whereIn('batch_order', \array_unique([0, $this->batchOrder]))
                ->orderBy('batch_order')
                ->get();

            $validated = $task->validate(...$inputModels->map->toInput()->all());

            $query = $this->databaseTask->inputs()->whereIn('batch_order', \array_unique([0, $this->batchOrder]));

            if ($validated) {
                $query->whereNull('validated_at')->update(['validated_at' => now()]);
            } else {
                $query->update(['validated_at' => null]);

                throw new \RuntimeException(__('database-task::tasks.task_validate_failed'));
            }

            Events\TaskValidated::dispatch($this->databaseTask, $this->batchOrder);
        } catch (\Throwable $e) {
            $this->databaseTask->toFailed($e->getMessage());

            Events\TaskValidateFailed::dispatch($this->databaseTask, $this->batchOrder, $e);
        }
    }

    protected function shouldSkip(): bool
    {
        return parent::shouldSkip() && $this->databaseTask->status !== Enums\TaskStatus::VALIDATING;
    }
}
