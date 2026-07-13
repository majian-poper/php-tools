<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;

class ListApprovalFlows extends ListRecords
{
    public static function getResource(): string
    {
        return FilamentApprovalFlowPlugin::getResourceClass();
    }

    public function getTabs(): array
    {
        return FilamentApprovalFlowPlugin::get()->getTabs();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
