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

    public function showOutputs(): bool
    {
        return true;
    }

    public function preview(Contracts\InputInterface ...$inputs): Htmlable
    {
        $previewHtml = $this->handlePreview($this->filterInputs(...$inputs));

        if (\is_string($previewHtml)) {
            $previewHtml = Str::of($previewHtml)->toHtmlString();
        }

        return $previewHtml;
    }

    public function validate(Contracts\InputInterface ...$inputs): bool
    {
        return $this->handleValidate($this->filterInputs(...$inputs));
    }

    public function run(Contracts\InputInterface ...$inputs): Contracts\OutputInterface
    {
        return $this->handleRun($this->filterInputs(...$inputs));
    }

    public function getBatchableInputs(Contracts\InputInterface ...$inputs): iterable
    {
        return $this->handleGetBatchableInputs($this->filterInputs(...$inputs));
    }

    public function mergeBatchableOutputs(Contracts\BatchableOutput ...$batchableOutputs): Contracts\OutputInterface
    {
        $sortedBatchableOutputs = collect($batchableOutputs)->sortBy(
            static fn(Contracts\BatchableOutput $output): int => $output->getBatchOrder()
        );

        return $this->handleMergeBatchableOutputs($sortedBatchableOutputs);
    }

    protected function filterInputs(Contracts\InputInterface ...$inputs): Collection
    {
        if ($this instanceof Contracts\BatchableTask) {
            $inputs = collect($inputs)
                ->sortByDesc(
                    static fn(Contracts\InputInterface $input): int => $input instanceof Contracts\BatchableInput
                        ? $input?->getBatchOrder() ?? 0
                        : 0
                )
                ->groupBy->getName()
                ->map->first();
        } else {
            $inputs = collect($inputs)->keyBy->getName();
        }

        return $this->filterAndFillInputs(collect(static::getSupportInputs())->keyBy->getName(), $inputs);
    }

    protected function filterAndFillInputs(Collection $supportInputs, Collection $inputs): Collection
    {
        $this->filteredInputs = collect();
        $this->namedInputInjections = collect();
        $this->typedInputInjections = collect();

        /** @var Contracts\InputInterface | HasValidation $supportInput */
        foreach ($supportInputs as $name => $supportInput) {
            $input = $inputs->pull($name);

            if (\in_array(HasValidation::class, class_uses_recursive($supportInput), true)) {
                if ($supportInput->isRequired() && \is_null($input?->getValue())) {
                    throw new \InvalidArgumentException(__('validation.required', ['attribute' => $supportInput->getLabel()]));
                }
            }

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

    protected function handlePreview(Collection $filteredInputs): Htmlable | string
    {
        throw new \LogicException(static::class . '::handlePreview() not implemented.');
    }

    protected function handleValidate(Collection $filteredInputs): bool
    {
        throw new \LogicException(static::class . '::handleValidate() not implemented.');
    }

    protected function handleRun(Collection $filteredInputs): Contracts\OutputInterface
    {
        throw new \LogicException(static::class . '::handleRun() not implemented.');
    }

    protected function handleGetBatchableInputs(Collection $filteredInputs): iterable
    {
        throw new \LogicException(static::class . '::handleGetBatchableInputs() not implemented.');
    }

    protected function handleMergeBatchableOutputs(Collection $batchableOutputs): Contracts\OutputInterface
    {
        throw new \LogicException(static::class . '::handleMergeBatchableOutputs() not implemented.');
    }
}
