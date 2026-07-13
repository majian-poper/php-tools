<?php

namespace PHPTools\LaravelDatabaseTask\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Outputs\FileOutput;
use PHPTools\LaravelDatabaseTask\Outputs\NullOutput;
use PHPTools\LaravelDatabaseTask\Outputs\TextOutput;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $database_task_id
 * @property string $output_class
 * @property string $output_value
 * @property bool $is_file
 * @property int $batch_order
 * @property \Carbon\CarbonImmutable | null $expires_at
 *
 * @property-read DatabaseTask $task
 */
class DatabaseTaskOutput extends Model implements HasMedia
{
    use Concerns\InteractsWithMedia;

    protected $casts = [
        'database_task_id' => 'int',
        'output_class' => 'string',
        'output_value' => 'string',
        'is_file' => 'bool',
        'batch_order' => 'int',
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'database_task_id',
        'output_class',
        'output_value',
        'is_file',
        'batch_order',
        'expires_at',
    ];

    protected ?Contracts\OutputInterface $outputInstance = null;

    protected ?\SplFileObject $cachedFile = null;

    public static function booted(): void
    {
        static::created(
            static function (self $model): void {
                if (! $model->is_file) {
                    return;
                }

                if (! $model->cachedFile?->isReadable()) {
                    return;
                }

                $model->addMedia($model->cachedFile->getRealPath())->toMediaCollection();
            }
        );
    }

    public static function fromOutput(Contracts\OutputInterface $output, ?DatabaseTask $databaseTask = null): static
    {
        $value = $output->getValue();
        $isFile = $value instanceof \SplFileObject && $value->isReadable();
        $batchOrder = $output instanceof Contracts\BatchableOutput ? $output->getBatchOrder() : 0;

        if ($isFile && $value->getSize() === 0) {
            return static::fromOutput(NullOutput::create()->batchOrder($batchOrder), $databaseTask);
        }

        $model = static::query()->make(
            [
                'output_class' => \get_class($output),
                'output_value' => $isFile ? '' : DatabaseTaskFacade::valueToString($value),
                'is_file' => $isFile,
                'batch_order' => $batchOrder,
                'expires_at' => $output->getExpiresAt(),
            ]
        );

        if (filled($databaseTask)) {
            $model->task()->associate($databaseTask);
        }

        $model->outputInstance = $output;
        $model->cachedFile = $isFile ? $value : null;

        return $model;
    }

    public function toOutput(): Contracts\OutputInterface
    {
        if (isset($this->outputInstance)) {
            return $this->outputInstance;
        }

        // TODO try-catch

        $isFile = \is_a($this->output_class, FileOutput::class, true) && $this->is_file && $this->file;

        if ($isFile) {
            $filename = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . $this->file->file_name;

            \touch($filename);

            $parameters = ['filename' => $filename, 'mode' => 'w+'];
        }

        /** @var FileOutput | TextOutput | NullOutput $output */
        $output = app($this->output_class, $parameters ?? []);

        if ($isFile && \method_exists($output, 'stream')) {
            $output->stream(fn() => $this->file->stream());
        }

        if (\method_exists($output, 'value')) {
            $output->value($this->output_value);
        }

        if ($output instanceof Contracts\BatchableOutput && \method_exists($output, 'batchOrder')) {
            $output->batchOrder($this->batch_order);
        }

        if (\method_exists($output, 'expiresAt')) {
            $output->expiresAt($this->expires_at);
        }

        return $this->outputInstance = $output;
    }

    // --- Expiration Management ---

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? false;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

    // --- Relationships ---

    public function task(): BelongsTo
    {
        return $this->belongsTo(DatabaseTaskFacade::resolveModelClass(DatabaseTask::class), 'database_task_id');
    }
}
