<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Models;

class ProcessTask extends BatchJob implements ShouldQueue
{
    public function __construct(Models\DatabaseTask $databaseTask, protected readonly int $batchOrder)
    {
        parent::__construct($databaseTask);
    }

    public function handle(): void
    {
        Events\TaskProcessing::dispatch($this->databaseTask, $this->batchOrder);

        try {
            $task = $this->getTask();

            $inputs = $this->databaseTask->inputs()
                ->whereIn('batch_order', \array_unique([0, $this->batchOrder]))
                ->orderBy('batch_order')
                ->get()
                ->map->toInput();

            $output = $task->run(...$inputs->all());

            $this->saveOutput($task, $output);

            Events\TaskProcessed::dispatch($this->databaseTask, $this->batchOrder);
        } catch (\Throwable $e) {
            $this->databaseTask->moveToFailedStatus($e->getMessage());

            Events\TaskProcessFailed::dispatch($this->databaseTask, $this->batchOrder, $e);
        }
    }

    protected function saveOutput(Contracts\TaskInterface $task, Contracts\OutputInterface $output): bool
    {
        if (! $task instanceof Contracts\BatchableTask) {
            return $this->databaseTask->moveToProcessedStatus($output);
        }

        if (! $output instanceof Contracts\BatchableOutput) {
            throw new \RuntimeException(__('database-task::tasks.errors.output_not_batchable'));
        }

        if ($output->getBatchOrder() !== $this->batchOrder) {
            throw new \RuntimeException(__('database-task::tasks.errors.output_batch_order_mismatch'));
        }

        return $this->databaseTask->saveOutput($output);
    }

    protected function shouldSkip(): bool
    {
        return parent::shouldSkip() && $this->databaseTask->status !== Enums\TaskStatus::PROCESSING;
    }
}
