<?php

namespace PHPTools\LaravelDatabaseTask\Enums;

enum TaskStatus: string
{
    use Concerns\HasLabel;

    case CREATED = 'created';

    case VALIDATING = 'validating';

    case VALIDATED = 'validated';

    case REQUESTED = 'requested';

    case READY = 'ready';

    case PROCESSING = 'processing';

    case PROCESSED = 'processed';

    case FAILED = 'failed';

    public function getFilamentColor(): string
    {
        return match ($this) {
            static::CREATED => 'gray',
            static::VALIDATING => 'info',
            static::VALIDATED => 'success',
            static::REQUESTED => 'warning',
            static::READY => 'info',
            static::PROCESSING => 'primary',
            static::PROCESSED => 'success',
            static::FAILED => 'danger',
        };
    }
}
