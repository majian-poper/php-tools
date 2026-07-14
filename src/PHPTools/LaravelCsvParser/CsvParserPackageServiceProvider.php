<?php

namespace PHPTools\LaravelCsvParser;

use Illuminate\Support\ServiceProvider;

class CsvParserPackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/files/config/csv-parser.php', 'csv-parser');
    }

    public function boot(): void
    {
        $this->bootConfigs();
        $this->bootMigrations();
    }

    protected function bootConfigs(): void
    {
        $vendorConfig = __DIR__ . '/files/config/csv-parser.php';

        if ($this->app->runningInConsole()) {
            $this->publishes([$vendorConfig => config_path('csv-parser.php')], 'csv-parser-config');
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

        $this->publishes($publishes, 'csv-parser-migrations');
    }
}
