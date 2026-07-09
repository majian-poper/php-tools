<?php

namespace PHPTools\LaravelFilamentApproval;

use Filament\Contracts\Plugin;
use Illuminate\Contracts\Auth\Authenticatable;
use PHPTools\Approval\Contracts\Approver;

class FilamentApprovalTaskPlugin implements Plugin
{
    use Concerns\InteractsWithPlugin;

    protected bool | \Closure $approverMode = false;

    protected bool | \Closure $canBeRolledBack = false;

    protected bool | \Closure $withApprovalsRelation = true;

    protected array | \Closure $currentApprovers = [];

    protected null | Authenticatable | \Closure $currentUser = null;

    protected ?\Closure $valuesMask = null;

    public static function getPluginId(): string
    {
        return 'laravel-filament-approval-task';
    }

    public static function getResourceClass(): string
    {
        if (Helper::getFilamentVersion() === 3) {
            return Resources\ApprovalTasks\ApprovalTaskResourceV3::class;
        }

        return Resources\ApprovalTasks\ApprovalTaskResourceV4::class;
    }

    public static function getRouteSlug(): string
    {
        return 'approval-tasks';
    }

    public function approverMode(bool | \Closure $condition = true): static
    {
        $this->approverMode = $condition;

        return $this;
    }

    public function isApproverMode(): bool
    {
        return (bool) $this->evaluate($this->approverMode);
    }

    public function canBeRolledBack(bool | \Closure $condition = true): static
    {
        $this->canBeRolledBack = $condition;

        return $this;
    }

    public function isCanBeRolledBack(): bool
    {
        return (bool) $this->evaluate($this->canBeRolledBack);
    }

    public function withApprovalsRelation(bool | \Closure $condition = true): static
    {
        $this->withApprovalsRelation = $condition;

        return $this;
    }

    public function isWithApprovalsRelation(): bool
    {
        return (bool) $this->evaluate($this->withApprovalsRelation);
    }

    public function currentApprovers(array | \Closure $approvers): static
    {
        $this->currentApprovers = $approvers;

        return $this;
    }

    public function getCurrentApprovers(): array
    {
        return \array_filter(
            (array) $this->evaluate($this->currentApprovers),
            static fn($approver): bool => $approver instanceof Approver
        );
    }

    public function currentUser(null | Authenticatable | \Closure $user): static
    {
        $this->currentUser = $user;

        return $this;
    }

    public function getCurrentUser(): ?Authenticatable
    {
        $user = $this->evaluate($this->currentUser);

        return $user instanceof Authenticatable ? $user : null;
    }

    public function valuesMask(\Closure $mask): static
    {
        $this->valuesMask = $mask;

        return $this;
    }

    public function getValuesMask(): \Closure
    {
        return $this->valuesMask ??= static fn(array $values): array => $values;
    }
}
