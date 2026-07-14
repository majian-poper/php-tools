<?php

namespace PHPTools\LaravelFilamentApproval;

use Illuminate\Support\ServiceProvider;

class FilamentApprovalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootTranslations();
    }

    protected function bootTranslations(): void
    {
        $vendorTranslations = __DIR__ . '/files/lang';

        $appTranslations = \function_exists('lang_path')
            ? lang_path('vendor/filament-approval')
            : resource_path('lang/vendor/filament-approval');

        $this->loadTranslationsFrom($vendorTranslations, 'filament-approval');

        if ($this->app->runningInConsole()) {
            $this->publishes([$vendorTranslations => $appTranslations], 'filament-approval-translations');
        }
    }
}
