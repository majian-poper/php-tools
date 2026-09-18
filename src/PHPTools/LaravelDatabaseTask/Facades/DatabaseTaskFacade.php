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
 * @template RemoteFile of \Spatie\MediaLibrary\Support\RemoteFile
 * @template LivewireUploadedFile of \Livewire\Features\SupportFileUploads\TemporaryUploadedFile
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @method static class-string<TModel> resolveModelClass(class-string<TModel> $modelClass)
 * @method static TModel resolveModel(class-string<TModel> $modelClass)
 * @method static InputModel arrayToInput(array $input, int $batchOrder = 0)
 * @method static InputModel arrayToInputModel(array $input, int $batchOrder = 0, ?TaskModel $databaseTask = null)
 * @method static InputModel toInputModel(InputInterface $input, ?TaskModel $databaseTask = null)
 * @method static OutputModel toOutputModel(OutputInterface $output, ?TaskModel $databaseTask = null)
 * @method static RemoteFile livewireUploadedFileToRemoteFile(LivewireUploadedFile $uploadedFile)
 * @method static string valueToString(mixed $value)
 * @method static bool valueIsFile(mixed $value)
 */
class DatabaseTaskFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DatabaseTaskManager::class;
    }
}
