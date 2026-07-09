<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Concerns;

use Filament\Panel;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTask;
use PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Pages;
use PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Tables;

trait InteractsWithDatabaseTasks
{
    public static function getSlug(?Panel $panel = null): string
    {
        return 'database-tasks';
    }

    public static function getNavigationSort(): ?int
    {
        return DatabaseTaskPlugin::get()->getNavigationSort();
    }

    public static function getNavigationIcon(): ?string
    {
        return DatabaseTaskPlugin::get()->getNavigationIcon();
    }

    public static function getModel(): string
    {
        return DatabaseTaskFacade::resolveModelClass(DatabaseTask::class);
    }

    public static function getModelLabel(): string
    {
        return __('database-task::model.database_task.label');
    }

    public static function getEloquentQuery(): Builder
    {
        return DatabaseTaskPlugin::get()->modifyQuery(
            parent::getEloquentQuery()
                ->withoutGlobalScopes([SoftDeletingScope::class])
                ->whereMorphedTo('user', Auth::user())
        );
    }

    public static function table(Table $table): Table
    {
        return DatabaseTaskPlugin::get()->configTable(
            Tables\DatabaseTasksTable::configure($table)
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDatabaseTasks::route('/'),
            'list' => Pages\ListDatabaseTasks::route('/list'),
            'create' => Pages\CreateDatabaseTask::route('/create/{task_class}'),
            'view' => Pages\ViewDatabaseTask::route('/{record}'),
        ];
    }
}
