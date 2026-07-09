<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas;
use Livewire\Attributes\On;
use PHPTools\Approval\Models\ApprovalTask;
use PHPTools\LaravelFilamentApproval\FilamentApprovalTaskPlugin;

/**
 * @property ApprovalTask $record
 */
class ViewApprovalTask extends ViewRecord
{
    public static function getResource(): string
    {
        return FilamentApprovalTaskPlugin::getResourceClass();
    }

    #[On('refreshViewApprovalTask')]
    public function refreshViewApprovalTask(): void
    {
        // emit by StepsRelationManager or ApprovalsRelationManager
    }

    protected function getHeaderActions(): array
    {
        $plugin = FilamentApprovalTaskPlugin::get();

        if (! $plugin->isApproverMode()) {
            return [];
        }

        $user = $plugin->getCurrentUser();

        if (! isset($user)) {
            return [];
        }

        if ($this->record->canStatusBeChangedBy($user)) {
            return [
                $this->getApproveAction(),
                $this->getRejectAction(),
            ];
        }

        if ($plugin->isCanBeRolledBack() && $this->record->canBeRolledBackBy($user)) {
            return [
                $this->getRollbackAction(),
            ];
        }

        return [];
    }

    protected function getApproveAction(): Actions\Action
    {
        return $this->getApprovalTaskAction('approve')
            ->form($this->commentFormSchema('approve'))
            ->defaultColor('success')
            ->icon('heroicon-m-check')
            ->action(
                function (Actions\Action $action, Forms\Form | Schemas\Schema $form, ApprovalTask $record): void {
                    $state = $form->getState();

                    try {
                        $record->approve($state['comment'] ?? '');
                    } catch (\Exception $e) {
                        $action->failureNotificationTitle($e->getMessage());

                        $action->failure();

                        return;
                    }

                    $action->success();
                }
            );
    }

    protected function getRejectAction(): Actions\Action
    {
        return $this->getApprovalTaskAction('reject')
            ->form($this->commentFormSchema('reject'))
            ->defaultColor('danger')
            ->icon('heroicon-m-x-mark')
            ->action(
                function (Actions\Action $action, Forms\Form | Schemas\Schema $form, ApprovalTask $record): void {
                    $state = $form->getState();

                    try {
                        $record->reject($state['comment'] ?? '');
                    } catch (\Exception $e) {
                        $action->failureNotificationTitle($e->getMessage());

                        $action->failure();

                        return;
                    }

                    $action->success();
                }
            );
    }

    protected function getRollbackAction(): Actions\Action
    {
        return $this->getApprovalTaskAction('rollback')
            ->defaultColor('warning')
            ->icon('heroicon-m-arrow-uturn-left')
            ->action(
                function (Actions\Action $action, ApprovalTask $record): void {
                    try {
                        $record->rollBack();
                    } catch (\Exception $e) {
                        $action->failureNotificationTitle($e->getMessage());

                        $action->failure();

                        return;
                    }

                    $action->success();
                }
            );
    }

    protected function getApprovalTaskAction(string $action): Actions\Action
    {
        return Actions\Action::make($action)
            ->requiresConfirmation()
            ->label(__("filament-approval::approvals.actions.{$action}.single.label"))
            ->modalHeading(fn(): string => __("filament-approval::approvals.actions.{$action}.single.modal.heading", ['label' => $this->getRecordTitle()]))
            ->modalSubmitActionLabel(__("filament-approval::approvals.actions.{$action}.single.modal.actions.{$action}.label"))
            ->successNotificationTitle(__("filament-approval::approvals.actions.{$action}.single.notifications.success.title"))
            ->after(
                function (): void {
                    $this->dispatch('refreshApprovalSteps');
                    $this->dispatch('refreshApprovals');
                }
            );
    }

    protected function commentFormSchema(string $action): array
    {
        return [
            Forms\Components\TextInput::make('comment')
                ->label(__("filament-approval::approvals.actions.{$action}.single.modal.form.comment"))
                ->maxLength(255),
        ];
    }
}
