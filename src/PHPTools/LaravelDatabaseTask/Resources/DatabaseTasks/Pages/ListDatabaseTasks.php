<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;
use PHPTools\LaravelDatabaseTask\Resources\DatabaseTaskClasses\DatabaseTaskClassResource;

class ListDatabaseTasks extends ListRecords
{
    public static function getResource(): string
    {
        return DatabaseTaskPlugin::getResourceClass();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->url(DatabaseTaskClassResource::getUrl('index')),
        ];
    }
}
