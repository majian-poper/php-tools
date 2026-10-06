<?php

namespace PHPTools\LaravelDatabaseTask\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums;
use PHPTools\LaravelDatabaseTask\Events;

class ProcessTask extends BatchJob implements ShouldQueue
{
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

            if (\method_exists($output, 'batchOrder')) {
                $output->batchOrder($this->batchOrder);
            }

            $this->saveOutput($task, $output);

            Events\TaskProcessed::dispatch($this->databaseTask, $this->batchOrder);
        } catch (\Throwable $e) {
            $this->databaseTask->toFailed($e->getMessage());

            Events\TaskProcessFailed::dispatch($this->databaseTask, $this->batchOrder, $e);
        }
    }

    protected function saveOutput(Contracts\TaskInterface $task, Contracts\OutputInterface $output): bool
    {
        if (! $task instanceof Contracts\BatchableTask) {
            return $this->databaseTask->toProcessed($output);
        }

        if (! $output instanceof Contracts\BatchableOutput) {
            throw new \RuntimeException(__('database-task::tasks.errors.output_not_batchable'));
        }

        return $this->databaseTask->saveOutput($output);
    }

    protected function shouldSkip(): bool
    {
        return parent::shouldSkip() && $this->databaseTask->status !== Enums\TaskStatus::PROCESSING;
    }
}
