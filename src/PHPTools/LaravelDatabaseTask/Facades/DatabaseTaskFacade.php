<?php

namespace PHPTools\LaravelDatabaseTask\Facades;

use Illuminate\Support\Facades\Facade;
use PHPTools\LaravelDatabaseTask\DatabaseTaskManager;

/**
 * @mixin \PHPTools\LaravelDatabaseTask\DatabaseTaskManager
 * @see \PHPTools\LaravelDatabaseTask\DatabaseTaskManager
 *
 * @template InputInterface of \PHPTools\LaravelDatabaseTask\Contracts\InputInterface
 * @template OutputInterface of \PHPTools\LaravelDatabaseTask\Contracts\OutputInterface
 * @template TaskModel of \PHPTools\LaravelDatabaseTask\Models\DatabaseTask
 * @template InputModel of \PHPTools\LaravelDatabaseTask\Models\DatabaseTaskInput
 * @template OutputModel of \PHPTools\LaravelDatabaseTask\Models\DatabaseTaskOutput
 * @template T of \Illuminate\Database\Eloquent\Model
 *
 * @method static class-string<T> resolveModelClass(class-string<T> $modelClass)
 * @method static T resolveModel(class-string<T> $modelClass)
 * @method static InputModel fromInput(InputInterface $input, ?TaskModel $databaseTask = null)
 * @method static InputModel fromInputArray(array $input, int $batchOrder = 0, ?TaskModel $databaseTask = null)
 * @method static OutputModel fromOutput(OutputInterface $output, ?TaskModel $databaseTask = null)
 * @method static string valueToString(mixed $value)
 */
class DatabaseTaskFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DatabaseTaskManager::class;
    }
}
