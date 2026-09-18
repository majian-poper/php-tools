<?php

namespace PHPTools\LaravelDatabaseTask;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PHPTools\LaravelDatabaseTask\Inputs\FileInput;
use Spatie\MediaLibrary\Support\RemoteFile;

class DatabaseTaskManager
{
    /**
     * The configuration repository instance.
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    public function __construct(Application $app)
    {
        $this->config = $app->make('config');
    }

    /**
     * @template T of Model
     * @param class-string<T> $modelClass
     * @return class-string<T>
     */
    public function resolveModelClass(string $modelClass): string
    {
        if (! (\class_exists($modelClass) && \is_subclass_of($modelClass, Model::class, true))) {
            throw new \InvalidArgumentException("Model class {$modelClass} does not exist.");
        }

        $key = Str::snake(class_basename($modelClass));

        $configModelClass = $this->config->get("database-task.implementations.{$key}", $modelClass);

        if (\is_a($configModelClass, $modelClass, true)) {
            $modelClass = $configModelClass;
        }

        return $modelClass;
    }

    /**
     * @template T of Model
     * @param class-string<T> $modelClass
     * @return T
     */
    public function resolveModel(string $modelClass): Model
    {
        return new ($this->resolveModelClass($modelClass));
    }

    public function arrayToInput(array $data, int $batchOrder = 0): ?Contracts\InputInterface
    {
        if (! Arr::has($data, ['input_class', 'input_value', 'is_file', 'is_excluded'])) {
            return null;
        }

        $inputClass = $data['input_class'];

        if (! (\class_exists($inputClass) && \is_subclass_of($inputClass, Contracts\InputInterface::class, true))) {
            return null;
        }

        /*
        * input_value 可能是以下类型：
        * AsQuery         => string                   e.g. "SELECT * FROM users"
        * AsNumber        => float                    e.g. 123.0
        *  |- multiple    => int string with comma    e.g. "1,2,3"
        * AsBoolean       => bool                     e.g. true | false
        * AsSelect        => array<string | int>      e.g. ["abc", "def"] | [1, 2, 3]
        * AsDateTime      => string                   e.g. "2023-01-01 00:00:00" | "2023-01-01"
        * AsFile          => TemporaryUploadedFile | \SplFileObject.
        */

        $value = $data['input_value'];

        /** @var FileInput | Contracts\InputInterface $input */
        $input = app($inputClass);

        if (\method_exists($input, 'value')) {
            $input->value($value);
        }

        if (\method_exists($input, 'excluded')) {
            $input->excluded($data['is_excluded'] ?? false);
        }

        if ($input instanceof Contracts\BatchableInput && \method_exists($input, 'batchOrder')) {
            $input->batchOrder($batchOrder);
        }

        return $input;
    }

    public function arrayToInputModel(array $data, int $batchOrder = 0, ?Models\DatabaseTask $databaseTask = null): ?Models\DatabaseTaskInput
    {
        $input = $this->arrayToInput($data, $batchOrder);

        return \is_null($input) ? null : $this->toInputModel($input, $databaseTask);
    }

    public function toInputModel(Contracts\InputInterface $input, ?Models\DatabaseTask $databaseTask = null): Models\DatabaseTaskInput
    {
        return $this->resolveModelClass(Models\DatabaseTaskInput::class)::fromInput($input, $databaseTask);
    }

    public function toOutputModel(Contracts\OutputInterface $output, ?Models\DatabaseTask $databaseTask = null): Models\DatabaseTaskOutput
    {
        return $this->resolveModelClass(Models\DatabaseTaskOutput::class)::fromOutput($output, $databaseTask);
    }

    public function livewireUploadedFileToRemoteFile(TemporaryUploadedFile $uploadedFile): RemoteFile
    {
        $invader = invade($uploadedFile);

        return new RemoteFile($invader->__get('path'), $invader->__get('disk'));
    }

    public function valueIsFile(mixed $value): bool
    {
        if ($value instanceof TemporaryUploadedFile) {
            return true;
        }

        if ($value instanceof \SplFileObject && $value->isReadable()) {
            return true;
        }

        return false;
    }

    /**
     * @param null | bool | int | string | \DateTime | TemporaryUploadedFile | \SplFileObject | iterable $value
     */
    public function valueToString(mixed $value): string
    {
        return match (true) {
            \is_null($value) => '',
            \is_string($value), \is_numeric($value) => (string) $value,
            \is_bool($value) => $value ? '1' : '0',
            \is_iterable($value) => \implode(',', \iterator_to_array($value)),
            $this->valueIsFile($value) => '',
            $value instanceof \DateTimeInterface => $value->format('Y-m-d H:i:s'),
            default => throw new \InvalidArgumentException('Unsupported value type.'),
        };
    }
}
