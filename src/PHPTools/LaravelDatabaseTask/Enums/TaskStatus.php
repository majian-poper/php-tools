<?php

namespace PHPTools\LaravelDatabaseTask\Enums;

enum TaskStatus: string
{
    use Concerns\HasLabel;

    case VALIDATING = 'validating';

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
            static::VALIDATING => 'info',
            static::UNAPPLIED => 'gray',
            static::PENDING => 'warning',
            static::PROCESSING => 'primary',
            static::APPROVED => 'success',
            static::PROCESSED => 'success',
            static::REJECTED => 'danger',
            static::FAILED => 'danger',
        };
    }
}
