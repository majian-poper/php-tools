<?php

namespace PHPTools\LaravelDatabaseTask\Concerns\Output;

use Filament\Support\Concerns\EvaluatesClosures;

trait HasExpires
{
    use EvaluatesClosures;

    protected null | \DateTimeInterface | \Closure $expiresAt = null;

    public function expiresAt(\DateTimeInterface | \Closure $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->evaluate($this->expiresAt);
    }
}
