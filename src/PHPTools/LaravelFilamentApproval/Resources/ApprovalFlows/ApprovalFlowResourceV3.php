<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows;

use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;

class ApprovalFlowResourceV3 extends Resource
{
    use Concerns\InteractsWithApprovalFlows;

    public static function form(Form $form): Form
    {
        return Schemas\ApprovalFlowForm::configure($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return FilamentApprovalFlowPlugin::get()->configInfolist(
            Schemas\ApprovalFlowInfolist::configure($infolist)
        );
    }
}
