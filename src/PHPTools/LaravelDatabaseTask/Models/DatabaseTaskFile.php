<?php

namespace PHPTools\LaravelDatabaseTask\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property-read string $client_original_name
 */
class DatabaseTaskFile extends Media
{
    protected function clientOriginalName(): Attribute
    {
        return Attribute::get(
            fn(mixed $value, array $attributes): string => $attributes['name'] . '.' . $this->extension
        );
    }
}
