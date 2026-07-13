<?php

namespace PHPTools\LaravelFilamentApproval;

use Filament\Contracts\Plugin;

class FilamentApprovalFlowPlugin implements Plugin
{
    use Concerns\InteractsWithPlugin;

    protected int $maxExpirationDays = 7;

    protected array | \Closure $approvableModels = [];

    protected array | \Closure $approverModels = [];

    protected string $approverTitleAttribute = 'name';

    public static function getPluginId(): string
    {
        return 'laravel-filament-approval-flow';
    }

    public static function getResourceClass(): string
    {
        if (Helper::getFilamentVersion() === 3) {
            return Resources\ApprovalFlows\ApprovalFlowResourceV3::class;
        }

        return Resources\ApprovalFlows\ApprovalFlowResourceV4::class;
    }

    public static function getRouteSlug(): string
    {
        return 'approval-flows';
    }

    public function maxExpirationDays(int $days): static
    {
        if ($days < 1) {
            $days = 1;
        }

        $this->maxExpirationDays = $days;

        return $this;
    }

    public function getExpirationDays(): array
    {
        return \array_flip(
            \array_map(
                static fn(int $day): int => $day * 86400,
                \array_combine($range = \range(1, $this->maxExpirationDays, 1), $range)
            )
        );
    }

    public function approvableModels(array | \Closure $approvableModels): static
    {
        $this->approvableModels = $approvableModels;

        return $this;
    }

    public function getApprovableModels(): array
    {
        return (array) $this->evaluate($this->approvableModels);
    }

    public function approverModels(array | \Closure $approverModels): static
    {
        $this->approverModels = $approverModels;

        return $this;
    }

    public function getApproverModels(): array
    {
        return (array) $this->evaluate($this->approverModels);
    }

    public function approverTitleAttribute(string $attribute): static
    {
        $this->approverTitleAttribute = $attribute;

        return $this;
    }

    public function getApproverTitleAttribute(): string
    {
        return $this->approverTitleAttribute;
    }
}
