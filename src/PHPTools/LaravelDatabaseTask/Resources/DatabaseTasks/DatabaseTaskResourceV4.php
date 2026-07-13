<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;

class DatabaseTaskResourceV4 extends Resource
{
    use Concerns\InteractsWithDatabaseTasks;

    public static function form(Schema $schema): Schema
    {
        return Schemas\DatabaseTaskForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DatabaseTaskPlugin::get()->configInfolist(
            Schemas\DatabaseTaskInfolist::configure($schema)
        );
    }
}
