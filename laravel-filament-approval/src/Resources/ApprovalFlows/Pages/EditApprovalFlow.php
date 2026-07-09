<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Pages;

use Filament\Resources\Pages\EditRecord;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;

class EditApprovalFlow extends EditRecord
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
