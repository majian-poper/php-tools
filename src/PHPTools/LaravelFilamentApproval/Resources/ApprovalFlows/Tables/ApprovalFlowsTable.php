<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Tables;

use Filament\Actions;
use Filament\Tables;
use PHPTools\Approval\Enums\ApprovalFlowType;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;
use PHPTools\LaravelFilamentApproval\Helper;

class ApprovalFlowsTable
{
    public static function configure(Tables\Table $table): Tables\Table
    {
        return $table
            ->filters(static::filters())
            ->columns(static::columns())
            ->actions(static::actions())
            ->defaultSort('id', 'desc');
    }

    protected static function filters(): array
    {
        return FilamentApprovalFlowPlugin::get()->getFilters();
    }

    protected static function columns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label(__('filament-approval::model.id')),
            Tables\Columns\TextColumn::make('name')
                ->label(__('filament-approval::model.approval_flow.name')),
            Tables\Columns\SelectColumn::make('approvable_type')
                ->label(__('filament-approval::model.approval_flow.approvable_type'))
                ->options(FilamentApprovalFlowPlugin::get()->getApprovableModels())
                ->disabled(),
            Tables\Columns\SelectColumn::make('flow_type')
                ->label(__('filament-approval::model.approval_flow.flow_type'))
                ->options(ApprovalFlowType::options())
                ->disabled(),
            Tables\Columns\TextColumn::make('expiration')
                ->label(__('filament-approval::model.approval_flow.expiration_days'))
                ->formatStateUsing(static fn(?int $state) => $state ? ($state / 86400) : null),
        ];
    }

    protected static function actions(): array
    {
        return [
            static::getViewAction(),
            static::getEditAction(),
            static::getDeleteAction(),
        ];
    }

    /**
     * @return Tables\Actions\ViewAction | Actions\ViewAction
     */
    protected static function getViewAction()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Tables\Actions\ViewAction::make();
        }

        return Actions\ViewAction::make();
    }

    /**
     * @return Tables\Actions\EditAction | Actions\EditAction
     */
    protected static function getEditAction()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Tables\Actions\EditAction::make();
        }

        return Actions\EditAction::make();
    }

    /**
     * @return Tables\Actions\DeleteAction | Actions\DeleteAction
     */
    protected static function getDeleteAction()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Tables\Actions\DeleteAction::make();
        }

        return Actions\DeleteAction::make();
    }
}
