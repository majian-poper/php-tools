<?php

namespace PHPTools\LaravelDatabaseTask;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function fromInputArray(array $input, int $batchOrder = 0, ?Models\DatabaseTask $databaseTask = null): ?Models\DatabaseTaskInput
    {
        return $this->resolveModelClass(Models\DatabaseTaskInput::class)::fromArray($input, $batchOrder, $databaseTask);
    }

    public function fromInput(Contracts\InputInterface $input, ?Models\DatabaseTask $databaseTask = null): ?Models\DatabaseTaskInput
    {
        return $this->resolveModelClass(Models\DatabaseTaskInput::class)::fromInput($input, $databaseTask);
    }

    public function fromOutput(Contracts\OutputInterface $output, ?Models\DatabaseTask $databaseTask = null): ?Models\DatabaseTaskOutput
    {
        return $this->resolveModelClass(Models\DatabaseTaskOutput::class)::fromOutput($output, $databaseTask);
    }

    /**
     * @param null | bool | int | string | \DateTime | \SplFileObject | iterable $value
     */
    public function valueToString(mixed $value): string
    {
        return match (true) {
            \is_null($value) => '',
            \is_string($value), \is_numeric($value) => (string) $value,
            \is_bool($value) => $value ? '1' : '0',
            \is_iterable($value) => \implode(',', \iterator_to_array($value)),
            $value instanceof \SplFileObject => '',
            $value instanceof \DateTimeInterface => $value->format('Y-m-d H:i:s'),
            default => throw new \InvalidArgumentException('Unsupported value type.'),
        };
    }
}
