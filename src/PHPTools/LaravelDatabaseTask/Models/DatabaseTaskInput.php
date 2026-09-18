<?php

namespace PHPTools\LaravelDatabaseTask\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Inputs\FileInput;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $database_task_id
 * @property string $input_class
 * @property string $input_value
 * @property bool $is_file
 * @property bool $is_excluded
 * @property int $batch_order
 */
class DatabaseTaskInput extends Model implements HasMedia
{
    use Concerns\InteractsWithMedia;

    protected $casts = [
        'database_task_id' => 'int',
        'input_class' => 'string',
        'input_value' => 'string',
        'is_file' => 'bool',
        'is_excluded' => 'bool',
        'batch_order' => 'int',
    ];

    protected $fillable = [
        'database_task_id',
        'input_class',
        'input_value',
        'is_file',
        'is_excluded',
        'batch_order',
    ];

    protected ?Contracts\InputInterface $inputInstance = null;

    protected TemporaryUploadedFile | \SplFileObject | null $cachedFile = null;

    public static function booted(): void
    {
        static::created(
            static function (self $model): void {
                if (! $model->is_file) {
                    return;
                }

                if (! DatabaseTaskFacade::valueIsFile($model->cachedFile)) {
                    return;
                }

                $fileAddr = $model->addMedia($model->cachedFile->getRealPath());

                if ($model->cachedFile instanceof TemporaryUploadedFile) {
                    $fileAddr->setFile(DatabaseTaskFacade::livewireUploadedFileToRemoteFile($model->cachedFile))
                        ->setName(\pathinfo($model->cachedFile->getClientOriginalName(), \PATHINFO_FILENAME))
                        ->setFileName(Str::uuid()->toString() . '.' . $model->cachedFile->getClientOriginalExtension());
                } else {
                    $fileAddr->setFileName(Str::uuid()->toString() . '.' . $model->cachedFile->getExtension());
                }

                $fileAddr->toMediaCollection();
            }
        );
    }

    // --- DatabaseTask ---

    public static function fromInput(Contracts\InputInterface $input, ?DatabaseTask $databaseTask = null): static
    {
        $value = ($input instanceof FileInput ? $input->getRawValue() : null) ?? $input->getValue();
        $isFile = DatabaseTaskFacade::valueIsFile($value);
        $batchOrder = $input instanceof Contracts\BatchableInput ? $input->getBatchOrder() : 0;

        $model = static::query()->make(
            [
                'input_class' => \get_class($input),
                'input_value' => DatabaseTaskFacade::valueToString($value),
                'is_file' => $isFile,
                'is_excluded' => $input->isExcluded(),
                'batch_order' => $batchOrder,
            ]
        );

        if (filled($databaseTask)) {
            $model->task()->associate($databaseTask);
        }

        $model->inputInstance = $input;
        $model->cachedFile = $isFile ? $value : null;

        return $model;
    }

    public function toInput(): Contracts\InputInterface
    {
        if (isset($this->inputInstance)) {
            return $this->inputInstance;
        }

        $isFile = \is_a($this->input_class, FileInput::class, true) && $this->is_file && $this->file;

        /** @var FileInput | Contracts\InputInterface $input */
        $input = app($this->input_class);

        if ($isFile) {
            $input->asFile()->stream(fn() => $this->file->stream());
        }

        if (\method_exists($input, 'value')) {
            $input->value($isFile ? null : $this->input_value);
        }

        if (\method_exists($input, 'excluded')) {
            $input->excluded($this->is_excluded);
        }

        if ($input instanceof Contracts\BatchableInput && \method_exists($input, 'batchOrder')) {
            $input->batchOrder($this->batch_order);
        }

        return $this->inputInstance = $input;
    }

    // --- Relationships ---

    public function task(): BelongsTo
    {
        return $this->belongsTo(DatabaseTaskFacade::resolveModelClass(DatabaseTask::class), 'database_task_id');
    }
}
