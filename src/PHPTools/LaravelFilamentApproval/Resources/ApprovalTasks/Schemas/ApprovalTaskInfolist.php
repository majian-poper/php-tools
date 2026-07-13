<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalTasks\Schemas;

use Filament\Infolists;
use Filament\Schemas;
use Illuminate\Support\Str;
use PHPTools\Approval\Contracts\ColumnResolver;
use PHPTools\Approval\Enums\ApprovalStatus;
use PHPTools\LaravelFilamentApproval\Helper;
use UAParser\Parser;

class ApprovalTaskInfolist
{
    /**
     * @param  Infolists\Infolist | Schemas\Schema  $infolist
     * @return Infolists\Infolist | Schemas\Schema
     */
    public static function configure($infolist)
    {
        return $infolist->schema(static::schema())->columns(1);
    }

    protected static function schema(): array
    {
        return [
            static::section()
                ->schema(static::descriptionSectionSchema())
                ->columnSpanFull(),
            static::section()
                ->schema(static::taskSectionSchema())
                ->columns(2),
        ];
    }

    /**
     * @return Infolists\Components\Section | Schemas\Components\Section
     */
    protected static function section()
    {
        if (Helper::getFilamentVersion() === 3) {
            return Infolists\Components\Section::make();
        }

        return Schemas\Components\Section::make();
    }

    protected static function descriptionSectionSchema(): array
    {
        return [
            Infolists\Components\TextEntry::make('title')
                ->label(__('filament-approval::model.approval_task.title'))
                ->inlineLabel(),
            Infolists\Components\TextEntry::make('description')
                ->label(__('filament-approval::model.approval_task.description'))
                ->html()
                ->visible(static fn($state): bool => filled($state)),
        ];
    }

    protected static function taskSectionSchema(): array
    {
        $schema = [
            Infolists\Components\TextEntry::make('user.name')
                ->label(__('filament-approval::model.approval_task.user'))
                ->inlineLabel(),
            Infolists\Components\TextEntry::make('status')
                ->label(__('filament-approval::model.approval_task.status'))
                ->inlineLabel()
                ->badge()
                ->color(
                    static fn(ApprovalStatus $state): string => match ($state) {
                        ApprovalStatus::PENDING => 'warning',
                        ApprovalStatus::APPROVING => 'success',
                        ApprovalStatus::APPROVED => 'success',
                        ApprovalStatus::REJECTED => 'danger',
                        ApprovalStatus::ROLLING_BACK => 'warning',
                        ApprovalStatus::ROLLED_BACK => 'warning',
                    }
                )
                ->formatStateUsing(static fn(ApprovalStatus $state) => $state->getLabel()),
            Infolists\Components\TextEntry::make('approved_at')
                ->label(__('filament-approval::model.approval_task.approved_at'))
                ->inlineLabel()
                ->badge()
                ->color('success'),
            Infolists\Components\TextEntry::make('rolled_back_at')
                ->label(__('filament-approval::model.approval_task.rolled_back_at'))
                ->inlineLabel()
                ->badge()
                ->color('warning'),
            Infolists\Components\TextEntry::make('expires_at')
                ->label(__('filament-approval::model.approval_task.expires_at'))
                ->inlineLabel()
                ->visible(static fn(?string $state): bool => filled($state)),
            Infolists\Components\TextEntry::make('created_at')
                ->label(__('filament-approval::model.created_at'))
                ->inlineLabel(),
            Infolists\Components\TextEntry::make('updated_at')
                ->label(__('filament-approval::model.updated_at'))
                ->inlineLabel(),
        ];

        foreach (config('approval.column_resolvers', []) as $resolver) {
            if (! \is_subclass_of($resolver, ColumnResolver::class)) {
                continue;
            }

            $name = \call_user_func([$resolver, 'name']);

            $schema[] = Infolists\Components\TextEntry::make($name)
                ->label(__("filament-approval::model.approval_task.{$name}"))
                ->inlineLabel()
                ->visible(static fn($state): bool => filled($state) && \is_string($state))
                ->formatStateUsing(
                    static fn($state) => match ($name) {
                        'url' => Str::contains($state, 'artisan') ? 'Command' : $state,
                        'user_agent' => Parser::create()->parse($state)->os->toString(),
                        default => $state
                    }
                );
        }

        return $schema;
    }
}
