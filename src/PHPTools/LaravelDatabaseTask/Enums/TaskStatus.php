<?php

namespace PHPTools\LaravelDatabaseTask\Enums;

enum TaskStatus: string
{
    use Concerns\HasLabel;

    case UNAPPLIED = 'unapplied';

    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    case PROCESSING = 'processing';

    case PROCESSED = 'processed';

    case FAILED = 'failed';

    public function getFilamentColor(): string
    {
        return match ($this) {
            static::UNAPPLIED => 'gray',
            static::PENDING, static::PROCESSING => 'warning',
            static::APPROVED, static::PROCESSED => 'success',
            static::REJECTED, static::FAILED => 'danger',
        };
    }
}
