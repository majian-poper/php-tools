<?php

namespace PHPTools\LaravelDatabaseTask\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\Enums;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
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

    // --- DatabaseTask ---

    public static function fromArray(array $data, int $batchOrder = 0, ?DatabaseTask $databaseTask = null): ?static
    {
        if (! Arr::has($data, ['input_class', 'input_value', 'is_file', 'is_excluded'])) {
            return null;
        }

        $inputClass = $data['input_class'];

        if (! \class_exists($inputClass) || ! \is_subclass_of($inputClass, Contracts\InputInterface::class)) {
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
        * AsFile          => \SplFileObject.          TODO: 支持 file 格式
        */
        $inputValue = $data['input_value'];

        if (blank($inputValue)) {
            return null;
        }

        $model = static::query()->make(
            [
                'input_class' => $inputClass,
                'input_value' => DatabaseTaskFacade::valueToString($inputValue),
                'is_file' => false,
                'is_excluded' => \boolval($data['is_excluded'] ?? false),
                'batch_order' => $batchOrder,
            ]
        );

        if (filled($databaseTask)) {
            $model->task()->associate($databaseTask);
        }

        return $model;
    }

    public static function fromInput(Contracts\InputInterface $input, ?DatabaseTask $databaseTask = null): static
    {
        $model = static::query()->make(
            [
                'input_class' => \get_class($input),
                'input_value' => DatabaseTaskFacade::valueToString($input->getValue()),
                'is_file' => false,
                'is_excluded' => $input->isExcluded(),
                'batch_order' => $input instanceof Contracts\BatchableInput ? $input->getBatchOrder() : 0,
            ]
        );

        if (filled($databaseTask)) {
            $model->task()->associate($databaseTask);
        }

        $model->inputInstance = $input;

        return $model;
    }

    public function toInput(): Contracts\InputInterface
    {
        if (isset($this->inputInstance)) {
            return $this->inputInstance;
        }

        // TODO try-catch

        /** @var Contracts\InputInterface $input */
        $input = app($this->input_class);

        if (\method_exists($input, 'excluded')) {
            $input->excluded($this->is_excluded);
        }

        if ($this->is_file && $this->file && \method_exists($input, 'asFile')) {
            $input->asFile();
        }

        if (\method_exists($input, 'value')) {
            $input->value($this->stringToValue($this->input_value, $input->getType()));
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

    // --- Helpers ---

    /**
     * @return null | bool | int | string | \DateTime | iterable
     */
    protected function stringToValue(string $string, Enums\InputType $type): mixed
    {
        if ($type === Enums\InputType::NUMBER) {
            if (\is_numeric($string)) {
                return (int) $string;
            }

            if (\str_contains($string, ',')) {
                return \explode(',', $string);
            }

            return null;
        }

        return match ($type) {
            Enums\InputType::QUERY => $string ?: null,
            Enums\InputType::SELECT => \explode(',', $string),
            Enums\InputType::DATETIME => CarbonImmutable::parse($string),
            Enums\InputType::BOOLEAN => \in_array($string, ['1', 'true', 'yes'], true),
            Enums\InputType::FILE => fn() => $this->file->toTempFileObject(),
        };
    }
}
