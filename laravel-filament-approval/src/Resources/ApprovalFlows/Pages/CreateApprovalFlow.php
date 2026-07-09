<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Pages;

use Filament\Resources\Pages\CreateRecord;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;

class CreateApprovalFlow extends CreateRecord
{
    public static function getResource(): string
    {
        return FilamentApprovalFlowPlugin::getResourceClass();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
