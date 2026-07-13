<?php

namespace PHPTools\LaravelDatabaseTask;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use PHPTools\LaravelDatabaseTask\Resources\DatabaseTaskClasses\DatabaseTaskClassResource;

class DatabaseTaskPlugin implements Plugin
{
    use EvaluatesClosures;

    protected int $navigationSort = 0;

    protected string $navigationIcon = 'heroicon-o-clock';

    protected array | \Closure $tabs = [];

    protected array | \Closure $filters = [];

    protected ?\Closure $modifyQueryUsing = null;

    protected ?\Closure $configTableUsing = null;

    protected ?\Closure $configInfolistUsing = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        try {
            return filament(static::getPluginId());
        } catch (\Throwable) {
            // Non web environment fallback, e.g. during artisan commands
            return static::make();
        }
    }

    public static function getPluginId(): string
    {
        return 'laravel-database-task';
    }

    public static function getResourceClass(): string
    {
        if (static::getFilamentVersion() === 3) {
            return Resources\DatabaseTasks\DatabaseTaskResourceV3::class;
        }

        return Resources\DatabaseTasks\DatabaseTaskResourceV4::class;
    }

    public static function getFilamentVersion(): int
    {
        static $majorVersion;

        if (isset($majorVersion)) {
            return $majorVersion;
        }

        $versionParser = new VersionParser;

        foreach ([3, 4, 5] as $major) {
            if (InstalledVersions::satisfies($versionParser, 'filament/filament', "^{$major}.0")) {
                return $majorVersion = $major;
            }
        }

        throw new \RuntimeException('Unsupported Filament version.');
    }

    public function getId(): string
    {
        return static::getPluginId();
    }

    public function register(Panel $panel): void
    {
        $panel->resources(
            [
                DatabaseTaskClassResource::class,
                static::getResourceClass(),
            ]
        );
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function navigationSort(int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): int
    {
        return $this->navigationSort;
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigationIcon;
    }

    public function filters(array | \Closure $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function getFilters(): array
    {
        return (array) $this->evaluate($this->filters);
    }

    public function modifyQueryUsing(?\Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function modifyQuery(Builder $query): Builder
    {
        $this->evaluate($this->modifyQueryUsing, ['query' => $query]);

        return $query;
    }

    public function configTableUsing(?\Closure $callback): static
    {
        $this->configTableUsing = $callback;

        return $this;
    }

    public function configTable(Table $table): Table
    {
        $this->evaluate($this->configTableUsing, ['table' => $table]);

        return $table;
    }

    public function configInfolistUsing(?\Closure $callback): static
    {
        $this->configInfolistUsing = $callback;

        return $this;
    }

    /**
     * @param  \Filament\Infolists\Infolist | \Filament\Schemas\Schema $infolist
     *
     * @return \Filament\Infolists\Infolist | \Filament\Schemas\Schema
     */
    public function configInfolist($infolist)
    {
        $this->evaluate($this->configInfolistUsing, ['infolist' => $infolist]);

        return $infolist;
    }
}
