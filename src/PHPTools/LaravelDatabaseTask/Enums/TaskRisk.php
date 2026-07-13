<?php

namespace PHPTools\LaravelDatabaseTask\Enums;

enum TaskRisk: string
{
    use Concerns\HasLabel;

    case LOW = 'low';

    case MEDIUM = 'medium';

    case HIGH = 'high';

    public function getFilamentColor(): string
    {
        return match ($this) {
            static::LOW => 'success',
            static::MEDIUM => 'warning',
            static::HIGH => 'danger',
        };
    }
}
