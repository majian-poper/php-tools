<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTask;

/**
 * @property-read \PHPTools\LaravelDatabaseTask\Models\DatabaseTask $record
 */
class ViewDatabaseTask extends ViewRecord
{
    public static function getResource(): string
    {
        return DatabaseTaskPlugin::getResourceClass();
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getRequestAction(),
            $this->getPreviewAction(),
        ];
    }

    protected function getRequestAction(): Actions\Action
    {
        return Actions\Action::make('request')
            ->label(__('database-task::model.database_task.actions.request.label'))
            ->requiresConfirmation()
            ->authorize('create', static::getResource()::getModel())
            ->visible(static fn(DatabaseTask $record): bool => $record->requestable())
            ->action(
                static function (DatabaseTask $record) {
                    Notification::make()
                        ->when(
                            $record->request(),
                            static fn(Notification $notification): Notification => $notification
                                ->success()
                                ->title(__('database-task::model.database_task.actions.request.notifications.requested')),
                            static fn(Notification $notification): Notification => $notification
                                ->danger()
                                ->title(__('database-task::model.database_task.actions.request.notifications.request_failed'))
                        )
                        ->send();
                }
            );
    }

    protected function getPreviewAction(): Actions\Action
    {
        return Actions\Action::make('preview')
            ->label(__('database-task::model.database_task.actions.preview.label'))
            ->visible(static fn(DatabaseTask $record): bool => $record->previewable())
            ->modalContent(static fn(DatabaseTask $record): Htmlable => $record->preview())
            ->modalFooterActions([]);
    }
}
