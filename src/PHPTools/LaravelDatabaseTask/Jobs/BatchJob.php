<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\Skip;
use Illuminate\Queue\SerializesModels;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Models;

abstract class BatchJob
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public ?Contracts\TaskInterface $task;

    public int $timeout;

    public int $tries;

    public function __construct(protected readonly Models\DatabaseTask $databaseTask, protected readonly int $batchOrder = 0)
    {
        $this->task = $task = $databaseTask->toTask();

        $this->timeout = \method_exists($task, 'timeout') ? $task->timeout() : config('database-task.queue.timeout', 60);
        $this->tries = \method_exists($task, 'tries') ? $task->tries() : config('database-task.queue.tries', 3);
        $this->delay = $task instanceof Contracts\DelayableTask ? $task->delay($this->batchOrder) : 0;
    }

    public function displayName(): string
    {
        return \sprintf('%s: %s', class_basename($this), class_basename($this->databaseTask->task_class));
    }

    public function middleware(): array
    {
        return [Skip::when($this->shouldSkip())];
    }

    public function getTask(): Contracts\TaskInterface
    {
        if (! $this->task instanceof Contracts\TaskInterface) {
            throw new \RuntimeException(
                __(
                    'database-task::tasks.errors.task_class_not_found',
                    ['task_class' => $this->databaseTask->task_class]
                )
            );
        }

        return $this->task;
    }

    protected function shouldSkip(): bool
    {
        return ! $this->batching();
    }
}
