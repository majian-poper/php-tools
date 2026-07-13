<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use PHPTools\Approval\Enums\ApprovalStatus;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    protected static bool $shouldSkipAuthorization = true;

    protected static bool $isLazy = false;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament-approval::model.approval_step.label');
    }

    #[On('refreshApprovalSteps')]
    public function refreshApprovalSteps(): void
    {
        // emit by ViewApprovalTask
    }

    public function hydrate(): void
    {
        $this->dispatch('refreshViewApprovalTask');
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->heading(__('filament-approval::model.approval_step.label'))
            ->modelLabel(__('filament-approval::model.approval_step.label'))
            ->columns($this->columns())
            ->modifyQueryUsing(static fn(Builder $query): Builder => $query->reorder())
            ->defaultGroup(
                static fn() => Tables\Grouping\Group::make('order_number')
                    ->label(__('filament-approval::model.approval_step.group_label'))
            )
            ->paginated(false)
            ->poll(fn(): ?string => $this->shouldPoll() ? '3s' : null);
    }

    public function getTableRecords(): EloquentCollection | Paginator | CursorPaginator
    {
        $records = parent::getTableRecords()
            ->sortBy('order_number')
            ->groupBy('order_number')
            ->map(
                static function (EloquentCollection $steps): EloquentCollection {
                    if ($steps->filter->isPending()->count() === $steps->count()) {
                        return $steps;
                    }

                    return $steps->reject->isPending();
                }
            )
            ->flatten();

        return EloquentCollection::make($records->all());
    }

    protected function columns(): array
    {
        return [
            Tables\Columns\TextColumn::make('approver.approver_title')
                ->label(__('filament-approval::model.approval_step.approver')),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->label(__('filament-approval::model.approval_step.status'))
                ->formatStateUsing(static fn(ApprovalStatus $state) => $state->getLabel())
                ->color(
                    static fn(ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::PENDING => 'warning',
                        ApprovalStatus::APPROVED => 'success',
                        ApprovalStatus::REJECTED => 'danger',
                        default => 'secondary',
                    }
                ),
            Tables\Columns\TextColumn::make('user.approver_title')
                ->label(__('filament-approval::model.approval_step.user')),
            Tables\Columns\TextColumn::make('comment')
                ->label(__('filament-approval::model.approval_step.comment')),
            Tables\Columns\TextColumn::make('approved_at')
                ->label(__('filament-approval::model.approval_step.approved_at'))
                ->dateTime('Y-m-d H:i:s'),
        ];
    }

    protected function shouldPoll(): bool
    {
        return $this->ownerRecord->isApproving() || $this->ownerRecord->isRollingBack();
    }
}
