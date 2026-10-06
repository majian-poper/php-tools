<?php

namespace PHPTools\LaravelDatabaseTask\Outputs;

class ArrayOutput extends TextOutput
{
    public function __construct(array $array = [])
    {
        if (filled($array)) {
            parent::__construct(\json_encode($array, flags: \JSON_UNESCAPED_UNICODE));
        }
    }

    public function getArray(): array
    {
        $value = $this->getValue();

        if (\is_string($value)) {
            $decoded = \json_decode($value, true);

            if (\is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
