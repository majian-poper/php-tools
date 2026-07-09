<?php

namespace PHPTools\LaravelFilamentApproval\Resources\ApprovalFlows\Schemas;

use Filament\Forms;
use Filament\Schemas;
use PHPTools\Approval\Enums\ApprovalFlowType;
use PHPTools\LaravelFilamentApproval\FilamentApprovalFlowPlugin;

class ApprovalFlowForm
{
    /**
     * @param  Forms\Form | Schemas\Schema  $form
     * @return Forms\Form | Schemas\Schema
     */
    public static function configure($form)
    {
        return $form->schema(static::schema());
    }

    protected static function schema(): array
    {
        $plugin = FilamentApprovalFlowPlugin::get();

        return [
            Forms\Components\TextInput::make('name')
                ->label(__('filament-approval::model.approval_flow.name'))
                ->autofocus()
                ->required(),
            Forms\Components\Select::make('expiration')
                ->label(__('filament-approval::model.approval_flow.expiration_days'))
                ->options($plugin->getExpirationDays())
                ->required(),
            Forms\Components\Select::make('approvable_type')
                ->label(__('filament-approval::model.approval_flow.approvable_type'))
                ->options($plugin->getApprovableModels())
                ->required(),
            Forms\Components\Select::make('flow_type')
                ->label(__('filament-approval::model.approval_flow.flow_type'))
                ->options(ApprovalFlowType::options())
                ->required(),
            static::stepsGroup('steps_one', 1),
            static::stepsGroup('steps_two', 2),
            static::stepsGroup('steps_three', 3),
            static::stepsGroup('steps_four', 4),
            static::stepsGroup('steps_five', 5),
        ];
    }

    protected static function stepsGroup(string $relationship, int $orderNumber): Forms\Components\Repeater
    {
        $beforeSaving = static fn(array $data): array => \array_merge($data, ['order_number' => $orderNumber]);

        return Forms\Components\Repeater::make($relationship)
            ->label(__('filament-approval::model.approval_flow_step.group_label', ['order' => $orderNumber]))
            ->relationship($relationship)
            ->schema([static::approverModelSelect()])
            ->addActionLabel(__('filament-approval::model.approval_flow_step.add_approver'))
            ->grid(3)
            ->maxItems(3)
            ->defaultItems(0)
            ->columnSpanFull()
            ->mutateRelationshipDataBeforeCreateUsing($beforeSaving)
            ->mutateRelationshipDataBeforeSaveUsing($beforeSaving);
    }

    protected static function approverModelSelect(): Forms\Components\MorphToSelect
    {
        $plugin = FilamentApprovalFlowPlugin::get();

        $approverModels = $plugin->getApproverModels();
        $approverTitleAttribute = $plugin->getApproverTitleAttribute();

        $types = collect($approverModels)
            ->map(
                static fn(string $label, string $class) => Forms\Components\MorphToSelect\Type::make($class)
                    ->label($label)
                    ->titleAttribute($approverTitleAttribute)
            )
            ->all();

        return Forms\Components\MorphToSelect::make('approver')
            ->label(__('filament-approval::model.approval_flow_step.approver'))
            ->types($types)
            ->preload()
            ->required();
    }
}
