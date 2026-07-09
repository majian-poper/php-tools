<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Concerns;

use Filament\Panel;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;
use PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Pages;
use PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Tables;

trait InteractsWithApprovalFlows
{
    public static function getSlug(?Panel $panel = null): string
    {
        return FilamentApprovalFlowPlugin::getRouteSlug();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentApprovalFlowPlugin::get()->getNavigationSort();
    }

    public static function getNavigationIcon(): ?string
    {
        return FilamentApprovalFlowPlugin::get()->getNavigationIcon();
    }

    public static function getModel(): string
    {
        return config('approval.implementations.approval_flow');
    }

    public static function getModelLabel(): string
    {
        return __('filament-approval::model.approval_flow.label');
    }

    public static function getEloquentQuery(): Builder
    {
        return FilamentApprovalFlowPlugin::get()->modifyQuery(parent::getEloquentQuery());
    }

    public static function table(Table $table): Table
    {
        return FilamentApprovalFlowPlugin::get()->configTable(
            Tables\ApprovalFlowsTable::configure($table)
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovalFlows::route('/'),
            'create' => Pages\CreateApprovalFlow::route('/create'),
            'edit' => Pages\EditApprovalFlow::route('/{record}/edit'),
        ];
    }
}
