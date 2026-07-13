<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\RelationManagers;

use Filament\Actions;
use Filament\Infolists;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas;
use Filament\Support;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use PHPTools\Approval\Enums\ApprovableEvent;
use PHPTools\Approval\Models\Approval;
use PHPTools\LaravelFilamentApproval\FilamentApprovalTaskPlugin;
use PHPTools\LaravelFilamentApproval\Helper;

class ApprovalsRelationManager extends RelationManager
{
    protected static string $relationship = 'approvals';

    protected static bool $shouldSkipAuthorization = true;

    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament-approval::model.approval.label');
    }

    public function hydrate(): void
    {
        $this->dispatch('refreshViewApprovalTask');
    }

    #[On('refreshApprovals')]
    public function refreshApprovals(): void
    {
        // emit by ViewApprovalTask
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->heading(__('filament-approval::model.approval.label'))
            ->modelLabel(__('filament-approval::model.approval.label'))
            ->columns($this->columns())
            ->actions($this->actions())
            ->defaultSort('order_number', 'asc')
            ->poll(fn(): ?string => $this->shouldPoll() ? '3s' : null);
    }

    protected function columns(): array
    {
        return [
            Tables\Columns\TextColumn::make('order_number')
                ->label(__('filament-approval::model.approval.order_number')),
            Tables\Columns\TextColumn::make('approvable_title')
                ->label(__('filament-approval::model.approval.approvable')),
            Tables\Columns\TextColumn::make('event')
                ->label(__('filament-approval::model.approval.event'))
                ->formatStateUsing(static fn(ApprovableEvent $state) => $state->getLabel()),
            Tables\Columns\TextColumn::make('effected_at')
                ->label(__('filament-approval::model.approval.effected_at')),
            Tables\Columns\TextColumn::make('rolled_back_at')
                ->label(__('filament-approval::model.approval.rolled_back_at')),
        ];
    }

    protected function actions(): array
    {
        return [
            $this->getViewAction()->infolist($this->actionInfolistSchema(...)),
        ];
    }

    /**
     * @return Tables\Actions\ViewAction | Actions\ViewAction
     */
    protected function getViewAction()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Tables\Actions\ViewAction::make('view')
                ->modalWidth(static fn(): Support\Enums\MaxWidth => Support\Enums\MaxWidth::ScreenExtraLarge);
        }

        return Actions\ViewAction::make('view')
            ->modalWidth(static fn(): Support\Enums\Width => Support\Enums\Width::ScreenExtraLarge);
    }

    protected function actionInfolistSchema(): array
    {
        $valuesMask = FilamentApprovalTaskPlugin::get()->getValuesMask();

        return [
            Infolists\Components\TextEntry::make('approvable_title')
                ->label(__('filament-approval::model.approval.approvable'))
                ->inlineLabel(),
            Infolists\Components\TextEntry::make('event')
                ->label(__('filament-approval::model.approval.event'))
                ->inlineLabel()
                ->formatStateUsing(static fn(ApprovableEvent $state): string => $state->getLabel()),
            Infolists\Components\TextEntry::make('effected_at')
                ->label(__('filament-approval::model.approval.effected_at'))
                ->inlineLabel()
                ->badge()
                ->color('success'),
            Infolists\Components\TextEntry::make('rolled_back_at')
                ->label(__('filament-approval::model.approval.rolled_back_at'))
                ->inlineLabel()
                ->badge()
                ->color('warning'),
            $this->oldNewValuesGrid()
                ->schema($this->oldNewValuesGridSchema($valuesMask)),
            $this->nowValuesSection()
                ->heading(__('filament-approval::model.approval.now_values'))
                ->schema($this->nowValuesSectionSchema($valuesMask))
                ->collapsed()
                ->visible(static fn(Approval $record): bool => isset($record->approvable)),
        ];
    }

    /**
     * @return Infolists\Components\Grid | Schemas\Components\Grid
     */
    protected function oldNewValuesGrid()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Infolists\Components\Grid::make(2);
        }

        return Schemas\Components\Grid::make(2);
    }

    protected function oldNewValuesGridSchema(\Closure $valuesMask): array
    {
        return [
            Infolists\Components\KeyValueEntry::make('old_values')
                ->label(__('filament-approval::model.approval.old_values'))
                ->state(static fn(Approval $record): array => $valuesMask($record->old_values)),
            Infolists\Components\KeyValueEntry::make('new_values')
                ->label(__('filament-approval::model.approval.new_values'))
                ->state(static fn(Approval $record): array => $valuesMask($record->new_values)),

        ];
    }

    /**
     * @return Infolists\Components\Section | Schemas\Components\Section
     */
    protected function nowValuesSection()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Infolists\Components\Section::make();
        }

        return Schemas\Components\Section::make();
    }

    protected function nowValuesSectionSchema(\Closure $valuesMask): array
    {
        return [
            Infolists\Components\KeyValueEntry::make('approvable')
                ->state(static fn(Approval $record): array => $valuesMask($record->approvable->getAttributes())),
        ];
    }

    protected function shouldPoll(): bool
    {
        return $this->ownerRecord->isApproving() || $this->ownerRecord->isRollingBack();
    }
}
