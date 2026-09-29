<?php

namespace PHPTools\LaravelDatabaseTask\Models;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Inputs\FileInput;
use PHPTools\LaravelDatabaseTask\Outputs\TextOutput;

/**
 * @property string $user_type
 * @property int $user_id
 * @property string $task_class
 * @property string $title
 * @property string $description
 * @property Enums\TaskRisk $risk
 * @property Enums\TaskStatus $status
 * @property \Carbon\CarbonImmutable | null $schedules_at
 *
 * @property-read \Illuminate\Database\Eloquent\Model $user
 * @property-read \Illuminate\Database\Eloquent\Collection<DatabaseTaskInput> $inputs
 * @property-read \Illuminate\Database\Eloquent\Collection<DatabaseTaskOutput> $outputs
 */
class DatabaseTask extends Model
{
    use SoftDeletes;

    protected $casts = [
        'user_type' => 'string',
        'user_id' => 'integer',
        'task_class' => 'string',
        'title' => 'string',
        'description' => 'string',
        'risk' => Enums\TaskRisk::class,
        'status' => Enums\TaskStatus::class,
        'schedules_at' => 'immutable_datetime',
    ];

    protected $fillable = [
        'user_type',
        'user_id',
        'task_class',
        'title',
        'description',
        'risk',
        'status',
        'schedules_at',
    ];

    protected ?Contracts\TaskInterface $taskInstance = null;

    // --- DatabaseTask ---

    /**
     * @return Contracts\TaskInterface | \PHPTools\LaravelDatabaseTask\Concerns\InteractsWithTask | null
     */
    public function toTask(): ?Contracts\TaskInterface
    {
        if (isset($this->taskInstance)) {
            return $this->taskInstance;
        }

        try {
            $task = app($this->task_class);

            if (! $task instanceof Contracts\TaskInterface) {
                throw new \RuntimeException(
                    \sprintf(
                        'Task class %s must implement %s interface.',
                        $this->task_class,
                        Contracts\TaskInterface::class
                    )
                );
            }
        } catch (\Throwable $e) {
            $task = null;
        }

        return $this->taskInstance = $task;
    }

    // --- Task Inputs ---

    public function saveInputs(Contracts\InputInterface ...$inputs): bool
    {
        if (! $this->exists) {
            return false;
        }

        $task = $this->toTask();

        if (! $task instanceof Contracts\TaskInterface) {
            return false;
        }

        if (blank($inputs)) {
            return true;
        }

        $inputs = collect($inputs)->filter(
            function (Contracts\InputInterface $input): bool {
                return $input instanceof Contracts\BatchableInput ? $input->getBatchOrder() === 0 : true;
            }
        );

        if (\is_subclass_of($task, Contracts\BatchableTask::class)) {
            $inputs->push(...\iterator_to_array($task->getBatchableInputs(...$inputs->all())));
        }

        [$fileInputModels, $normalInputModels] = $inputs
            ->map(fn(Contracts\InputInterface $input) => DatabaseTaskFacade::toInputModel($input, $this))
            ->partition(static fn(DatabaseTaskInput $inputModel): bool => $inputModel->toInput() instanceof FileInput);

        return $this->transactionCallback(
            function () use ($fileInputModels, $normalInputModels): bool {
                $this->inputs()->delete();

                $fileInputsSaved = $fileInputModels->every(
                    static fn(DatabaseTaskInput $inputModel): bool => $inputModel->save()
                );

                if (! $fileInputsSaved) {
                    return false;
                }

                return $normalInputModels
                    ->map(static fn(DatabaseTaskInput $inputModel): array => $inputModel->getAttributes())
                    ->chunk(100)
                    ->every(fn(Collection $chunk) => $this->inputs()->fillAndInsert($chunk->all()));
            }
        );
    }

    // --- Task Outputs ---

    public function saveOutput(Contracts\OutputInterface | Contracts\BatchableOutput $output): bool
    {
        return $this->transactionCallback(
            function () use ($output): bool {
                $this->outputs()
                    ->where('batch_order', $output instanceof Contracts\BatchableOutput ? $output->getBatchOrder() : 0)
                    ->delete();

                return DatabaseTaskFacade::toOutputModel($output, $this)->save();
            }
        );
    }

    // --- Status Management ---

    public function moveToStatus(Enums\TaskStatus $to, ?Enums\TaskStatus $from = null): bool
    {
        return $this->transactionCallback(
            fn(): bool => $this->newQuery()
                ->whereKey($this->getKey())
                ->when(filled($from), static fn($query) => $query->where('status', $from))
                ->lockForUpdate()
                ->update(['status' => $to]) > 0
        );
    }

    public function moveToFailedStatus(string $reason): bool
    {
        return $this->transactionCallback(
            fn(): bool => $this->moveToStatus(Enums\TaskStatus::FAILED) && $this->saveOutput(new TextOutput($reason))
        );
    }

    public function moveToUnappliedStatus(): bool
    {
        return $this->transactionCallback(fn(): bool => $this->moveToStatus(Enums\TaskStatus::UNAPPLIED));
    }

    public function moveToProcessedStatus(Contracts\OutputInterface $output): bool
    {
        return $this->transactionCallback(
            fn(): bool => $this->moveToStatus(Enums\TaskStatus::PROCESSED) && $this->saveOutput($output)
        );
    }

    // --- Task actions ---

    public function previewable(): bool
    {
        return \in_array($this->status, [Enums\TaskStatus::VALIDATING, Enums\TaskStatus::UNAPPLIED, Enums\TaskStatus::PENDING]) || ! $this->outputs()->exists();
    }

    public function preview(): Htmlable
    {
        return $this->toTask()->preview(...$this->normal_inputs()->get()->map->toInput()->all());
    }

    public function requestable(): bool
    {
        return $this->status === Enums\TaskStatus::UNAPPLIED;
    }

    public function request(): bool
    {
        $result = $this->moveToStatus(Enums\TaskStatus::PENDING);

        if ($result) {
            Events\TaskRequested::dispatch($this, Auth::user());
        }

        return $result;
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->morphTo('user');
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(DatabaseTaskFacade::resolveModelClass(DatabaseTaskInput::class), 'database_task_id');
    }

    public function normal_inputs(): HasMany
    {
        return $this->inputs()->where('batch_order', 0);
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(DatabaseTaskFacade::resolveModelClass(DatabaseTaskOutput::class), 'database_task_id');
    }

    public function normal_outputs(): HasMany
    {
        return $this->outputs()->where('batch_order', 0);
    }

    /**
     * @param \Closure(): bool $callback
     */
    protected function transactionCallback(\Closure $callback): bool
    {
        $callback = fn() => throw_if(value($callback) === false, \RuntimeException::class, 'Callback returned false.');

        try {
            $this->getConnection()->transactionLevel() > 0
                ? $callback()
                : $this->getConnection()->transaction($callback);
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }
}
