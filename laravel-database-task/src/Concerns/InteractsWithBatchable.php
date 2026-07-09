<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

trait InteractsWithBatchable
{
    protected int $batchOrder = 0;

    public function getBatchOrder(): int
    {
        return $this->batchOrder;
    }

    public function batchOrder(int $batchOrder): self
    {
        if ($batchOrder < 0) {
            throw new \InvalidArgumentException('Batch order must be a non-negative integer.');
        }

        $this->batchOrder = $batchOrder;

        return $this;
    }
}
