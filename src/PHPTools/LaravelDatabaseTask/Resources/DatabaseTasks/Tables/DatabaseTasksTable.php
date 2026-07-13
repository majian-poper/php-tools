<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Tables;

use Filament\Actions;
use Filament\Tables;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;
use PHPTools\LaravelDatabaseTask\Enums;

class DatabaseTasksTable
{
    public static function configure(Tables\Table $table): Tables\Table
    {
        return $table
            ->filters(static::filters())
            ->columns(static::columns())
            ->recordActions(static::actions())
            ->defaultSort('id', 'desc');
    }

    protected static function filters(): array
    {
        return DatabaseTaskPlugin::get()->getFilters() ?: [
            Tables\Filters\TrashedFilter::make(),
        ];
    }

    protected static function columns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label(__('database-task::model.id'))
                ->sortable(),
            Tables\Columns\TextColumn::make('user.name')
                ->label(__('database-task::model.database_task.user')),
            Tables\Columns\TextColumn::make('title')
                ->label(__('database-task::model.database_task.title'))
                ->searchable(),
            Tables\Columns\TextColumn::make('risk')
                ->label(__('database-task::model.database_task.risk'))
                ->badge()
                ->color(static fn(Enums\TaskRisk $state): string => $state->getFilamentColor())
                ->formatStateUsing(static fn(Enums\TaskRisk $state): string => $state->getLabel()),
            Tables\Columns\TextColumn::make('status')
                ->label(__('database-task::model.database_task.status'))
                ->badge()
                ->color(static fn(Enums\TaskStatus $state): string => $state->getFilamentColor())
                ->formatStateUsing(static fn(Enums\TaskStatus $state): string => $state->getLabel()),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('database-task::model.created_at'))
                ->dateTime('Y-m-d H:i:s')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->label(__('database-task::model.updated_at'))
                ->dateTime('Y-m-d H:i:s')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected static function actions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
