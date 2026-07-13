<?php

namespace PHPTools\LaravelDatabaseTask\Enums;

enum InputType: string
{
    use Concerns\HasLabel;

    case QUERY = 'query';

    case NUMBER = 'number';

    case SELECT = 'select';

    case DATETIME = 'datetime';

    case BOOLEAN = 'boolean';

    case FILE = 'file';

    public function canBeMultiple(): bool
    {
        return match ($this) {
            static::NUMBER, static::SELECT => true,
            default => false,
        };
    }

    // 支持上传文件替换输入内容的类型
    public function canBeFile(): bool
    {
        return match ($this) {
            static::NUMBER, static::FILE => true,
            default => false,
        };
    }

    public function canBeExcluded(): bool
    {
        return match ($this) {
            static::NUMBER, static::SELECT => true,
            default => false,
        };
    }

    public function getPlaceholder(string $label, bool $isMultiple = false): string
    {
        $isMultiple = $this->canBeMultiple() && $isMultiple;

        return __(
            \sprintf('database-task::tasks.input_types.%s.placeholder%s', $this->value, $isMultiple ? '_multiple' : ''),
            ['label' => $label]
        );
    }

    public function getHelperText(string $label, bool $isMultiple = false): string
    {
        $isMultiple = $this->canBeMultiple() && $isMultiple;

        return __(
            \sprintf('database-task::tasks.input_types.%s.help_text%s', $this->value, $isMultiple ? '_multiple' : ''),
            ['label' => $label]
        );
    }
}
