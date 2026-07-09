<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPTools\LaravelDatabaseTask\Contracts;

/**
 * @method static array<Contracts\InputInterface> getSupportInputs()
 */
trait InteractsWithTask
{
    /** @var Collection<string, Contracts\InputInterface> */
    protected Collection $filteredInputs;

    /** @var Collection<string, Contracts\InputInterface> */
    protected Collection $namedInputInjections;

    /** @var Collection<string, Contracts\InputInterface> */
    protected Collection $typedInputInjections;

    public function getTitle(): string
    {
        $taskName = Str::of(static::class)->afterLast('\\')->snake();

        return __("database-task::tasks.title.{$taskName}");
    }

    public function preview(Contracts\InputInterface ...$inputs): Htmlable
    {
        return $this->handlePreview($this->filterInputs(...$inputs));
    }

    public function run(Contracts\InputInterface ...$inputs): Contracts\OutputInterface
    {
        return $this->handleRun($this->filterInputs(...$inputs));
    }

    public function showOutputs(): bool
    {
        return true;
    }

    protected function filterInputs(Contracts\InputInterface ...$inputs): Collection
    {
        return $this->filterAndFillInputs(
            collect(static::getSupportInputs())->keyBy->getName(),
            collect($inputs)->keyBy->getName()
        );
    }

    protected function filterAndFillInputs(Collection $supportInputs, Collection $inputs): Collection
    {
        $this->filteredInputs = collect();
        $this->namedInputInjections = collect();
        $this->typedInputInjections = collect();

        foreach ($supportInputs as $name => $_) {
            $input = $inputs->pull($name);

            $this->filteredInputs[$name] = $input;

            $this->namedInputInjections[Str::snake($name)] = $input;
            $this->namedInputInjections[Str::studly($name)] = $input;

            $this->typedInputInjections[\get_class($_)] = $input;
        }

        return $this->filteredInputs;
    }

    protected function namedInputInjections(): array
    {
        return $this->namedInputInjections->all();
    }

    protected function typedInputInjections(): array
    {
        return $this->typedInputInjections->all();
    }

    abstract protected function handlePreview(Collection $inputs): Htmlable;

    abstract protected function handleRun(Collection $inputs): Contracts\OutputInterface;
}
