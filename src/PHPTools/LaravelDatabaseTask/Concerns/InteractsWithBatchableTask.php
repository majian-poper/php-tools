<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

use Illuminate\Support\Collection;
use PHPTools\LaravelDatabaseTask\Contracts;

/**
 * @method static array<Contracts\InputInterface> getSupportInputs()
 */
trait InteractsWithBatchableTask
{
    use InteractsWithTask {
        filterInputs as interactsWithTaskFilterInputs;
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
        $supportInputs = collect(static::getSupportInputs())->keyBy->getName();

        $getBatchorder = static function (Contracts\InputInterface $input): int {
            return $input instanceof Contracts\BatchableInput ? $input->getBatchOrder() : 0;
        };

        $inputs = collect($inputs)
            ->groupBy->getName()
            ->map(
                static function (Collection $inputs) use ($getBatchorder): Contracts\InputInterface {
                    return $inputs->sortByDesc($getBatchorder)->first();
                }
            );

        return $this->filterAndFillInputs($supportInputs, $inputs);
    }

    abstract protected function handleGetBatchableInputs(Collection $filteredInputs): iterable;

    abstract protected function handleMergeBatchableOutputs(Collection $filteredOutputs): Contracts\OutputInterface;
}
