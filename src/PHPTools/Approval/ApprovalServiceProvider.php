<?php

namespace PHPTools\Approval;

use Illuminate\Support\ServiceProvider;

class ApprovalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/files/config/approval.php', 'approval');
    }

    public function boot(): void
    {
        $this->bootConfigs();
        $this->bootMigrations();
        $this->bootTranslations();
    }

    protected function bootConfigs(): void
    {
        $vendorConfig = __DIR__ . '/files/config/approval.php';

        if ($this->app->runningInConsole()) {
            $this->publishes([$vendorConfig => config_path('approval.php')], 'approval-config');
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

        $this->publishes($publishes, 'approval-migrations');
    }

    protected function bootTranslations(): void
    {
        $vendorTranslations = __DIR__ . '/files/lang';

        $appTranslations = \function_exists('lang_path')
            ? lang_path('vendor/approval')
            : resource_path('lang/vendor/approval');

        $this->loadTranslationsFrom($vendorTranslations, 'approval');

        if ($this->app->runningInConsole()) {
            $this->publishes([$vendorTranslations => $appTranslations], 'approval-translations');
        }
    }
}
