<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks;

use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use PHPTools\LaravelFilamentApproval\FilamentApprovalTaskPlugin;

class ApprovalTaskResourceV3 extends Resource
{
    use Concerns\InteractsWithApprovalTasks;

    public static function infolist(Infolist $infolist): Infolist
    {
        return FilamentApprovalTaskPlugin::get()->configInfolist(
            Schemas\ApprovalTaskInfolist::configure($infolist)
        );
    }
}
