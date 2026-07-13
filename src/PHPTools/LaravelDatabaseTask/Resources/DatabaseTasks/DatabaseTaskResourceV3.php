<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks;

use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;

class DatabaseTaskResourceV3 extends Resource
{
    use Concerns\InteractsWithDatabaseTasks;

    public static function form(Form $form): Form
    {
        return Schemas\DatabaseTaskForm::configure($form);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return DatabaseTaskPlugin::get()->configInfolist(
            Schemas\DatabaseTaskInfolist::configure($infolist)
        );
    }
}
