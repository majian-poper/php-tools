<?php

namespace PHPTools\LaravelDatabaseTask\Jobs\Concerns;

use Illuminate\Queue\Middleware\Skip;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums\TaskStatus;
use PHPTools\LaravelDatabaseTask\Models;

trait WithTask
{
    public readonly Models\DatabaseTask $databaseTask;

    public $timeout = 300; // 5 minutes

    public function middleware(): array
    {
        return [Skip::when($this->shouldSkip())];
    }

    public function setDatabaseTask(Models\DatabaseTask $databaseTask): void
    {
        $this->databaseTask = $databaseTask;
    }

    public function getDatabaseTask(): Models\DatabaseTask
    {
        return $this->databaseTask;
    }

    public function isProcessing(): bool
    {
        return $this->databaseTask->status === TaskStatus::PROCESSING;
    }

    public function getTask(): ?Contracts\TaskInterface
    {
        $task = $this->databaseTask->toTask();

        if (! $task instanceof Contracts\TaskInterface) {
            throw new \RuntimeException(
                __(
                    'database-task::tasks.errors.task_class_not_found',
                    ['task_class' => $this->databaseTask->task_class]
                )
            );
        }

        return $task;
    }

    protected function markAsFailed(Models\DatabaseTask $databaseTask, string $reason): void
    {
        $databaseTask->moveToFailedStatus($reason);
    }

    protected function shouldSkip()
    {
        return ! $this->isProcessing();
    }
}
