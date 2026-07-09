<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTaskClasses;

use Filament\Actions;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Tables;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTaskClass;

class DatabaseTaskClassResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'database-task-classes';
    }

    public static function getModel(): string
    {
        return DatabaseTaskFacade::resolveModelClass(DatabaseTaskClass::class);
    }

    public static function getModelLabel(): string
    {
        return __('database-task::model.database_task_class.label');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDatabaseTaskClasses::route('/'),
        ];
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns(
                [
                    Tables\Columns\TextColumn::make('title')
                        ->label(__('database-task::model.database_task.label'))
                        ->searchable()
                        ->sortable(),
                ]
            )
            ->recordActions(
                [
                    Actions\Action::make('request')
                        ->label(__('database-task::model.database_task_class.actions.create'))
                        ->url(static::createDatabaseTaskUrl(...)),
                ]
            )
            ->recordUrl(static::createDatabaseTaskUrl(...));
    }

    protected static function createDatabaseTaskUrl(DatabaseTaskClass $record): string
    {
        return DatabaseTaskPlugin::getResourceClass()::getUrl('create', ['task_class' => $record->md5]);
    }
}
