<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Schemas;

use Filament\Infolists;
use Filament\Schemas;
use PHPTools\Approval\Enums\ApprovalFlowType;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;
use PHPTools\LaravelFilamentApproval\Helper;

class ApprovalFlowInfolist
{
    /**
     * @param  Infolists\Infolist | Schemas\Schema  $infolist
     */
    public static function configure($infolist)
    {
        return $infolist->schema(static::schema());
    }

    protected static function schema(): array
    {
        return [
            static::basicSection()
                ->schema(static::basicSectionSchema())
                ->columns(2)
                ->columnSpanFull(),
            static::stepsGroup('steps_one', 1),
            static::stepsGroup('steps_two', 2),
            static::stepsGroup('steps_three', 3),
            static::stepsGroup('steps_four', 4),
            static::stepsGroup('steps_five', 5),
        ];
    }

    /**
     * @return Infolists\Components\Section | Schemas\Components\Section
     */
    protected static function basicSection()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Infolists\Components\Section::make();
        }

        return Schemas\Components\Section::make();
    }

    protected static function basicSectionSchema(): array
    {
        $approvableModels = FilamentApprovalFlowPlugin::get()->getApprovableModels();

        return [
            Infolists\Components\TextEntry::make('name')
                ->label(__('filament-approval::model.approval_flow.name'))
                ->inlineLabel(),
            Infolists\Components\TextEntry::make('expiration')
                ->label(__('filament-approval::model.approval_flow.expiration_days'))
                ->inlineLabel()
                ->formatStateUsing(static fn($state) => $state ? (int) ($state / 86400) : null),
            Infolists\Components\TextEntry::make('approvable_type')
                ->label(__('filament-approval::model.approval_flow.approvable_type'))
                ->inlineLabel()
                ->formatStateUsing(static fn(string $state): string => $approvableModels[$state] ?? $state),
            Infolists\Components\TextEntry::make('flow_type')
                ->label(__('filament-approval::model.approval_flow.flow_type'))
                ->inlineLabel()
                ->formatStateUsing(static fn(ApprovalFlowType $state) => $state->getLabel()),
        ];
    }

    protected static function stepsGroup(string $relationship, int $orderNumber): Infolists\Components\RepeatableEntry
    {
        return Infolists\Components\RepeatableEntry::make($relationship)
            ->label(__('filament-approval::model.approval_flow_step.group_label', ['order' => $orderNumber]))
            ->schema([static::approverEntry()])
            ->grid(3)
            ->columnSpanFull();
    }

    protected static function approverEntry(): Infolists\Components\TextEntry
    {
        return Infolists\Components\TextEntry::make('approver.approver_title')
            ->label(__('filament-approval::model.approval_flow_step.approver'));
    }
}
