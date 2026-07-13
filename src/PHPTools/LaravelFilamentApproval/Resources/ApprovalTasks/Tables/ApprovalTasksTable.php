<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\Tables;

use Filament\Actions;
use Filament\Tables;
use PHPTools\Approval\Enums\ApprovalFlowType;
use PHPTools\Approval\Enums\ApprovalStatus;
use PHPTools\Approval\Models\ApprovalTask;
use PHPTools\LaravelFilamentApproval\FilamentApprovalTaskPlugin;
use PHPTools\LaravelFilamentApproval\Helper;

class ApprovalTasksTable
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
        return FilamentApprovalTaskPlugin::get()->getFilters();
    }

    protected static function columns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label(__('filament-approval::model.id')),
            Tables\Columns\TextColumn::make('title')
                ->label(__('filament-approval::model.approval_task.title')),
            Tables\Columns\TextColumn::make('user.name')
                ->label(__('filament-approval::model.approval_task.user')),
            Tables\Columns\TextColumn::make('approvals_count')
                ->label(__('filament-approval::model.approval_task.approvals_count'))
                ->counts('approvals')
                ->suffix(' ' . __('filament-approval::model.count_suffix')),
            Tables\Columns\TextColumn::make('flow_type')
                ->label(__('filament-approval::model.approval_task.flow_type'))
                ->formatStateUsing(
                    static fn(ApprovalFlowType $state, ApprovalTask $record) => \sprintf(
                        '%s (%d / %d)',
                        $state->getLabel(),
                        $record->steps->filter->isApproved()->groupBy('order_number')->count(),
                        $record->steps->groupBy('order_number')->count()
                    )
                ),
            Tables\Columns\TextColumn::make('status')
                ->label(__('filament-approval::model.approval_task.status'))
                ->formatStateUsing(static fn(ApprovalStatus $state) => $state->getLabel())
                ->color(
                    static fn(ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::PENDING => 'warning',
                        ApprovalStatus::APPROVING => 'success',
                        ApprovalStatus::APPROVED => 'success',
                        ApprovalStatus::REJECTED => 'danger',
                        ApprovalStatus::ROLLING_BACK => 'warning',
                        ApprovalStatus::ROLLED_BACK => 'warning',
                    }
                )
                ->badge(),
            Tables\Columns\TextColumn::make('approved_at')
                ->label(__('filament-approval::model.approval_task.approved_at'))
                ->dateTime('Y-m-d H:i:s')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('rolled_back_at')
                ->label(__('filament-approval::model.approval_task.rolled_back_at'))
                ->dateTime('Y-m-d H:i:s')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('expires_at')
                ->label(__('filament-approval::model.approval_task.expires_at'))
                ->dateTime('Y-m-d H:i:s')
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('filament-approval::model.created_at'))
                ->dateTime('Y-m-d H:i:s'),
        ];
    }

    protected static function actions(): array
    {
        return [static::getViewAction()];
    }

    protected static function getViewAction()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Tables\Actions\ViewAction::make();
        }

        return Actions\ViewAction::make();
    }
}
