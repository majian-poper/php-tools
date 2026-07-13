<?php

namespace PHPTools\LaravelDatabaseTask\Contracts;

interface OutputInterface
{
    public function getValue(): null | string | \SplFileObject;

    public function getExpiresAt(): ?\DateTimeInterface;
}
