<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;

class ApprovalFlowResourceV4 extends Resource
{
    use Concerns\InteractsWithApprovalFlows;

    public static function form(Schema $schema): Schema
    {
        return Schemas\ApprovalFlowForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FilamentApprovalFlowPlugin::get()->configInfolist(
            Schemas\ApprovalFlowInfolist::configure($schema)
        );
    }
}
