<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Schemas;

use Filament\Forms;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Schemas;
use Filament\Schemas\Components\Utilities;
use Illuminate\Support\Str;
use PHPTools\LaravelDatabaseTask\Contracts\InputInterface;
use PHPTools\LaravelDatabaseTask\Contracts\TaskInterface;
use PHPTools\LaravelDatabaseTask\Enums\InputType;
use PHPTools\LaravelDatabaseTask\Enums\TaskRisk;

class DatabaseTaskForm
{
    /**
     * @param  Forms\Form | Schemas\Schema $form
     */
    public static function configure($form)
    {
        return $form
            ->components(static::components(...))
            ->columns(1);
    }

    protected static function components(): array
    {
        return [
            Forms\Components\Hidden::make('task_class'),
            Forms\Components\TextInput::make('title')
                ->label(__('database-task::model.database_task.title'))
                ->required(),
            Forms\Components\Select::make('risk')
                ->label(__('database-task::model.database_task.risk'))
                ->native(false)
                ->options(TaskRisk::getFilamentOptions())
                ->required(),
            Forms\Components\RichEditor::make('description')
                ->label(__('database-task::model.database_task.description'))
                ->fileAttachments(false)
                ->required(),
            Forms\Components\DateTimePicker::make('schedules_at')
                ->label(__('database-task::model.database_task.schedules_at'))
                ->helperText(Str::of(__('database-task::model.database_task.schedules_at_help_text'))->toHtmlString())
                ->native(false)
                ->displayFormat('Y-m-d H:i:s')
                ->after(now()),
            Schemas\Components\Group::make()
                ->schema(
                    static function (Utilities\Get $get): array {
                        $taskType = $get->string('task_class');

                        if (filled($taskType) && \is_subclass_of($taskType, TaskInterface::class)) {
                            return collect($taskType::getSupportInputs())
                                ->map(static::makeFieldsetFor(...))
                                ->all();
                        }

                        return [];
                    }
                )
        ];
    }

    /**
     * @param InputInterface | \PHPTools\LaravelDatabaseTask\Concerns\Input\HasNaming $input
     */
    protected static function makeFieldsetFor(InputInterface $input): Schemas\Components\Fieldset
    {
        return Schemas\Components\Fieldset::make()
            ->label($input->getLabel())
            ->statePath("inputs.{$input->getName()}")
            ->schema(
                [
                    Forms\Components\Hidden::make('input_class'),
                    static::makeInputValueField($input),
                    static::makeIsFileField($input),
                    static::makeIsExcludedField($input),
                ]
            )
            ->columns(1);
    }

    /**
     * @param InputInterface | \PHPTools\LaravelDatabaseTask\Concerns\InteractsWithInput $input
     */
    protected static function makeInputValueField(InputInterface $input): Forms\Components\Field
    {
        $inputType = $input->getType();

        $field = match ($inputType) {
            InputType::QUERY => Forms\Components\CodeEditor::make('input_value')->language(Language::Sql), // TODO: v3 compatibility?
            InputType::NUMBER => static::makeNumberField($input),
            InputType::SELECT => static::makeSelectField($input),
            InputType::DATETIME => static::makeDatetimeField($input),
            InputType::BOOLEAN => Forms\Components\Checkbox::make('input_value'),
            default => throw new \RuntimeException('Unsupported input field type: ' . $inputType->value),
        };

        $field->label($input->getLabel())
            ->when($inputType !== InputType::BOOLEAN)->hiddenLabel()
            ->when(\method_exists($field, 'placeholder'))->placeholder($input->getPlaceholder())
            ->helperText(Str::of($input->getHelperText())->toHtmlString())
            ->extraInputAttributes($input->getExtraInputAttributes())
            ->live(); // 同步更新 is_excluded 状态

        if ($input->isRequired()) {
            $field->required();
        } else if (filled($requiredWithoutAll = $input->requiredWithoutAllFields())) {
            // 多个字段必填其中一个, 例如 corporation_id 与 school_id 二选一必填
            $requiredWithoutAllFields = collect($requiredWithoutAll)
                ->map(static fn(string $name): string => "data.inputs.{$name}.input_value")
                ->all();

            $field->requiredWithoutAll($requiredWithoutAllFields, true);
        }

        return $field;
    }

    /**
     * @param InputInterface | \PHPTools\LaravelDatabaseTask\Concerns\Input\AsNumber $input
     */
    protected static function makeNumberField(InputInterface $input): Forms\Components\Field
    {
        return $input->isMultiple()
            ? Forms\Components\TagsInput::make('input_value')->splitKeys([' ', ','])->separator(',')
            : Forms\Components\TextInput::make('input_value')->integer();
    }

    /**
     * @param InputInterface | \PHPTools\LaravelDatabaseTask\Concerns\Input\AsSelect $input
     */
    protected static function makeSelectField(InputInterface $input): Forms\Components\Field
    {
        return Forms\Components\Select::make('input_value')
            ->options($input->getOptions())
            ->searchable(false)
            ->multiple($input->isMultiple());
    }

    /**
     * @param InputInterface | \PHPTools\LaravelDatabaseTask\Concerns\Input\AsDateTime $input
     */
    protected static function makeDatetimeField(InputInterface $input): Forms\Components\Field
    {
        return Forms\Components\DateTimePicker::make('input_value')
            ->time($input->hasTime())
            ->native(false)
            ->displayFormat($input->getDisplayFormat());
    }

    /**
     * TODO: 支持文件上传字段
     * @param InputInterface | \PHPTools\LaravelDatabaseTask\Concerns\Input\AsFile $input
     */
    protected static function makeIsFileField(InputInterface $input): Forms\Components\Field
    {
        return Forms\Components\Hidden::make('is_file')
            ->label(__('database-task::tasks.input_types.is_file'));
    }

    /**
     * TODO: 支持可排除字段
     * @param InputInterface | \PHPTools\LaravelDatabaseTask\Concerns\InteractsWithInput $input
     */
    protected static function makeIsExcludedField(InputInterface $input): Forms\Components\Field
    {
        return Forms\Components\Hidden::make('is_excluded')
            ->label(__('database-task::tasks.input_types.is_excluded'));

        // TODO: 支持可排除字段
        Forms\Components\Checkbox::make('is_excluded')
            ->label(__('database-task::tasks.input_types.is_excluded'))
            ->visible(
                static function (Forms\Components\Checkbox $component, Utilities\Get $get, Utilities\Set $set) use ($input): bool {
                    $isVisible = $input->isCanBeExcluded() && $component->isEnabled() && filled($get->array('input_value'));

                    if ($isVisible) {
                        $set('is_excluded', false);
                    }

                    return $isVisible;
                }
            )
            ->disabled(
                static function (Utilities\Get $get) use ($input): bool {
                    // 除 [自身] 以外的所有非 [对象外] 的 fieldset 的 input_value 都为空时, 当前 checkbox 禁用
                    $isDisabled = collect($get->array('../'))
                        ->reject(static fn(array $_, string $key): bool => $key === $input->getName())
                        ->reject(static fn(array $fieldset): bool => $fieldset['is_excluded'] ?? false)
                        ->pluck('input_value')
                        ->filter()
                        ->isEmpty();

                    return $isDisabled;
                }
            );
    }
}
