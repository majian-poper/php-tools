<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\Concerns;

use Filament\Panel;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PHPTools\LaravelFilamentApproval\FilamentApprovalTaskPlugin;
use PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\Pages;
use PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\RelationManagers;
use PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\Tables;

trait InteractsWithApprovalTasks
{
    public static function getSlug(?Panel $panel = null): string
    {
        return FilamentApprovalTaskPlugin::getRouteSlug();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentApprovalTaskPlugin::get()->getNavigationSort();
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentApprovalTaskPlugin::get()->getNavigationIcon();
    }

    public static function getModel(): string
    {
        return config('approval.implementations.approval_task');
    }

    public static function getModelLabel(): string
    {
        return __('filament-approval::model.approval_task.label');
    }

    public static function getEloquentQuery(): Builder
    {
        $builder = parent::getEloquentQuery()->with(['steps'])->whereHas('approvals');

        $plugin = FilamentApprovalTaskPlugin::get();

        if ($plugin->isApproverMode()) {
            $builder->when(
                filled($currentApprovers = $plugin->getCurrentApprovers()),
                static fn(Builder $query): Builder => $query->whereApprovers(...$currentApprovers),
                static fn(Builder $query): Builder => $query->whereRaw('1 = 0')
            );
        } else {
            $builder->whereMorphedTo('user', $plugin->getCurrentUser());
        }

        return $plugin->modifyQuery($builder);
    }

    public static function table(Table $table): Table
    {
        return FilamentApprovalTaskPlugin::get()->configTable(
            Tables\ApprovalTasksTable::configure($table)
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovalTasks::route('/'),
            'view' => Pages\ViewApprovalTask::route('/{record}/view'),
        ];
    }

    public static function getRelations(): array
    {
        $plugin = FilamentApprovalTaskPlugin::get();

        return \array_filter(
            [
                $plugin->isWithApprovalsRelation() ? RelationManagers\ApprovalsRelationManager::class : null,
                RelationManagers\StepsRelationManager::class,
            ]
        );
    }
}
