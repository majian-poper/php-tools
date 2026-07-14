<?php

namespace PHPTools\LaravelDatabaseTask;

use Illuminate\Support\ServiceProvider;

class DatabaseTaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/files/config/database-task.php', 'database-task');
    }

    public function boot(): void
    {
        $this->bootCommands();
        $this->bootConfigs();
        $this->bootMigrations();
        $this->bootTranslations();
    }

    protected function bootCommands(): void
    {
        $this->commands(Commands\DispatchApprovedTaskCommand::class);
    }

    protected function bootConfigs(): void
    {
        $vendorConfig = __DIR__ . '/files/config/database-task.php';

        if ($this->app->runningInConsole()) {
            $this->publishes([$vendorConfig => config_path('database-task.php')], 'database-task-config');
        }
    }

    protected function bootMigrations(): void
    {
        $vendorMigrations = __DIR__ . '/files/migrations';

        $this->loadMigrationsFrom($vendorMigrations);

        if (! $this->app->runningInConsole()) {
            return;
        }

        $publishes = [];
        $now = now();

        foreach (\glob($vendorMigrations . '/*.php') as $sourcePath) {
            $baseName = \preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', \basename($sourcePath));
            $len = \strlen($baseName);

            $targetPath = null;

            foreach (\glob(database_path('migrations/*.php')) as $existing) {
                if (\substr($existing, -$len) === $baseName) {
                    $targetPath = $existing;

                    break;
                }
            }

            $targetPath ??= database_path('migrations/' . $now->addSecond()->format('Y_m_d_His') . '_' . $baseName);

            $publishes[$sourcePath] = $targetPath;
        }

        $this->publishes($publishes, 'database-task-migrations');
    }

    protected function bootTranslations(): void
    {
        $vendorTranslations = __DIR__ . '/files/lang';

        $appTranslations = \function_exists('lang_path')
            ? lang_path('vendor/database-task')
            : resource_path('lang/vendor/database-task');

        $this->loadTranslationsFrom($vendorTranslations, 'database-task');

        if ($this->app->runningInConsole()) {
            $this->publishes([$vendorTranslations => $appTranslations], 'database-task-translations');
        }
    }
}
