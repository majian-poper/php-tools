<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use PHPTools\LaravelFilamentApproval\FilamentApprovalTaskPlugin;

class ApprovalTaskResourceV4 extends Resource
{
    use Concerns\InteractsWithApprovalTasks;

    public static function infolist(Schema $schema): Schema
    {
        return FilamentApprovalTaskPlugin::get()->configInfolist(
            Schemas\ApprovalTaskInfolist::configure($schema)
        );
    }
}
