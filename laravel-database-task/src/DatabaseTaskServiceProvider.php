<?php

namespace PHPTools\LaravelDatabaseTask;

use PHPTools\LaravelDatabaseTask\Commands\DispatchApprovedTaskCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DatabaseTaskServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-database-task')
            ->hasCommand(DispatchApprovedTaskCommand::class)
            ->hasConfigFile()
            ->hasTranslations()
            ->discoversMigrations();
    }
}
