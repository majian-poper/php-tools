<?php

namespace PHPTools\LaravelDatabaseTask\Enums\Concerns;

use Illuminate\Support\Str;

/**
 * @property-read string $name
 * @property-read int | string $value
 *
 * @method static array<self> cases()
 */
trait HasLabel
{
    public function getLabel(): string
    {
        $enumName = Str::of(static::class)->afterLast('\\')->snake();

        return __("database-task::enums.{$enumName}.{$this->name}");
    }

    public static function getFilamentOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(static fn(self $type): array => [$type->value => $type->getLabel()])
            ->toArray();
    }
}
