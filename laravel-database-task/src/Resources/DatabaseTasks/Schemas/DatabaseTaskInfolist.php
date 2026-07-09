<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Schemas;

use Filament\Actions\Action;
use Filament\Infolists;
use Filament\Schemas;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;
use PHPTools\LaravelDatabaseTask\Enums;
use PHPTools\LaravelDatabaseTask\Events;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTask;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTaskInput;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTaskOutput;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseTaskInfolist
{
    /**
     * @param  Infolists\Infolist | Schemas\Schema  $infolist
     *
     * @return Infolists\Infolist | Schemas\Schema
     */
    public static function configure($infolist)
    {
        return $infolist
            ->schema(static::schema())
            ->columns(1);
    }

    protected static function schema(): array
    {
        return [
            static::section()
                ->schema(static::descriptionSectionSchema()),
            static::section()
                ->schema(static::taskSectionSchema())
                ->columns(2),
            Infolists\Components\RepeatableEntry::make('normal_inputs')
                ->label(__('database-task::model.database_task.inputs'))
                ->schema(static::inputSectionSchema())
                ->visible(static fn(DatabaseTask $record): bool => $record->normal_inputs->isNotEmpty()),
            Infolists\Components\RepeatableEntry::make('normal_outputs')
                ->label(__('database-task::model.database_task.outputs'))
                ->schema(static::outputSectionSchema())
                ->visible(static fn(DatabaseTask $record): bool => $record->toTask()?->showOutputs() && $record->normal_outputs->isNotEmpty()),
        ];
    }

    /**
     * @return Infolists\Components\Section | Schemas\Components\Section
     */
    protected static function section()
    {
        if (DatabaseTaskPlugin::getFilamentVersion() === 3) {
            return Infolists\Components\Section::make();
        }

        return Schemas\Components\Section::make();
    }

    protected static function descriptionSectionSchema(): array
    {
        return [
            Infolists\Components\TextEntry::make('title')
                ->label(__('database-task::model.database_task.title'))
                ->inlineLabel(),
            Infolists\Components\TextEntry::make('description')
                ->label(__('database-task::model.database_task.description'))
                ->html()
        ];
    }

    protected static function taskSectionSchema(): array
    {
        return [
            Infolists\Components\TextEntry::make('task_class')
                ->label(__('database-task::model.database_task.task_class'))
                ->inlineLabel()
                ->formatStateUsing(static fn(string $state): string => (new $state)->getTitle()),
            Infolists\Components\TextEntry::make('risk')
                ->label(__('database-task::model.database_task.risk'))
                ->inlineLabel()
                ->color(static fn(Enums\TaskRisk $state): string => $state->getFilamentColor())
                ->formatStateUsing(static fn(Enums\TaskRisk $state): string => $state->getLabel()),
            Infolists\Components\TextEntry::make('status')
                ->label(__('database-task::model.database_task.status'))
                ->inlineLabel()
                ->badge()
                ->color(static fn(Enums\TaskStatus $state): string => $state->getFilamentColor())
                ->formatStateUsing(static fn(Enums\TaskStatus $state): string => $state->getLabel()),
            Infolists\Components\TextEntry::make('created_at')
                ->label(__('database-task::model.created_at'))
                ->inlineLabel()
                ->dateTime('Y-m-d H:i:s'),
            Infolists\Components\TextEntry::make('updated_at')
                ->label(__('database-task::model.updated_at'))
                ->inlineLabel()
                ->dateTime('Y-m-d H:i:s'),
            Infolists\Components\TextEntry::make('schedules_at')
                ->label(__('database-task::model.database_task.schedules_at'))
                ->inlineLabel()
                ->dateTime('Y-m-d H:i:s'),
        ];
    }

    protected static function inputSectionSchema(): array
    {
        return [
            Infolists\Components\TextEntry::make('input_value')
                ->label(static fn(DatabaseTaskInput $record): string => $record->toInput()->getLabel())
                ->formatStateUsing(
                    static function (DatabaseTaskInput $record, $state): string {
                        /** @var \PHPTools\LaravelDatabaseTask\Contracts\InputInterface | \PHPTools\LaravelDatabaseTask\Concerns\InteractsWithInput $input */
                        $input = $record->toInput();

                        return match ($input->getType()) {
                            Enums\InputType::BOOLEAN => __('database-task::tasks.input_types.boolean.' . ($input->getValue() ? 'true' : 'false')),
                            Enums\InputType::SELECT => \implode(', ', Arr::only($input->getOptions(), $input->getValue())),
                            Enums\InputType::DATETIME => $input->getValue()?->format($input->getDisplayFormat()),
                            Enums\InputType::FILE => $record->file?->file_name,
                            default => $state,
                        };
                    }
                )
        ];
    }

    protected static function outputSectionSchema(): array
    {
        return [
            Infolists\Components\TextEntry::make('output_value')
                ->label(__('database-task::model.database_task_output.output_value'))
                ->color('danger')
                ->visible(static fn(DatabaseTaskOutput $record): bool => ! $record->is_file && filled($record->output_value)),
            Infolists\Components\TextEntry::make('expires_at')
                ->label(__('database-task::model.database_task_output.expires_at'))
                ->inlineLabel()
                ->dateTime('Y-m-d H:i:s')
                ->color(static fn(DatabaseTaskOutput $record): string => $record->isValid() ? 'success' : 'danger')
                ->visible(static fn(DatabaseTaskOutput $record): bool => $record->is_file && filled($record->expires_at)),
            Schemas\Components\Group::make()
                ->schema(static fn(DatabaseTaskOutput $record): array => [static::toDownloadAction($record)])
                ->visible(static fn(DatabaseTaskOutput $record): bool => $record->is_file && $record->isValid()),
        ];
    }

    protected static function toDownloadAction(DatabaseTaskOutput $record): Action
    {
        return Action::make('download')
            ->label(__('database-task::model.database_task_output.actions.download'))
            ->visible(fn(): bool => $record->is_file && $record->isValid() && isset($record->file))
            ->before(fn() => Events\TaskOutputDownloading::dispatch($record, Auth::user()))
            ->action(fn(): StreamedResponse => $record->file->toResponse(request()));
    }
}
