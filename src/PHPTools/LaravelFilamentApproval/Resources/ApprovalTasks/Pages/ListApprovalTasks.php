<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\Pages;

use Filament\Resources\Pages\ListRecords;
use PHPTools\LaravelFilamentApproval\FilamentApprovalTaskPlugin;

class ListApprovalTasks extends ListRecords
{
    public static function getResource(): string
    {
        return FilamentApprovalTaskPlugin::getResourceClass();
    }

    public function getTabs(): array
    {
        return FilamentApprovalTaskPlugin::get()->getTabs();
    }
}
