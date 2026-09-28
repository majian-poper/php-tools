<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPTools\LaravelDatabaseTask\Concerns\Input\HasValidation;
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
        $previewHtml = $this->handlePreview($this->filterInputs(...$inputs));

        if (\is_string($previewHtml)) {
            $previewHtml = Str::of($previewHtml)->toHtmlString();
        }

        return $previewHtml;
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

        /** @var Contracts\InputInterface | HasValidation $supportInput */
        foreach ($supportInputs as $name => $supportInput) {
            if (\in_array(HasValidation::class, class_uses_recursive($supportInput), true)) {
                if ($supportInput->isRequired() && \is_null($inputs->get($name)?->getValue())) {
                    throw new \InvalidArgumentException(__('validation.required', ['attribute' => $supportInput->getLabel()]));
                }
            }

            $input = $inputs->pull($name);

            $this->filteredInputs[$name] = $input;
            $this->namedInputInjections[Str::snake($name)] = $input;
            $this->namedInputInjections[Str::studly($name)] = $input;
            $this->typedInputInjections[\get_class($supportInput)] = $input;
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

    abstract protected function handlePreview(Collection $inputs): Htmlable | string;

    abstract protected function handleRun(Collection $inputs): Contracts\OutputInterface;
}
