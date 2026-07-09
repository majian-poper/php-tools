<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTaskClasses\Pages;

use Filament\Resources\Pages\ListRecords;
use PHPTools\LaravelDatabaseTask\Resources\DatabaseTaskClasses\DatabaseTaskClassResource;

class ListDatabaseTaskClasses extends ListRecords
{
    protected static string $resource = DatabaseTaskClassResource::class;
}
