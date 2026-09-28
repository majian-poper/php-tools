<?php

namespace PHPTools\LaravelDatabaseTask\Resources\DatabaseTasks\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use PHPTools\LaravelDatabaseTask\Contracts;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;
use PHPTools\LaravelDatabaseTask\Enums;
use PHPTools\LaravelDatabaseTask\Facades\DatabaseTaskFacade;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTask;
use PHPTools\LaravelDatabaseTask\Models\DatabaseTaskClass;

class CreateDatabaseTask extends CreateRecord
{
    public static function getResource(): string
    {
        return DatabaseTaskPlugin::getResourceClass();
    }

    public function getTitle(): string | Htmlable
    {
        $taskClassComponent = collect($this->form->getComponents(withActions: false))->first(
            /** @var Forms\Components\Component | Schemas\Components\Component $component */
            static fn($component): bool => \method_exists($component, 'getName') && $component->getName() === 'task_class'
        );

        $state = $taskClassComponent?->getState();

        if (isset($state) && \is_subclass_of($state, Contracts\TaskInterface::class)) {
            return (new $state)->getTitle();
        }

        return parent::getTitle();
    }

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $taskClass = DatabaseTaskFacade::resolveModel(DatabaseTaskClass::class)
            ->newQuery()
            ->where('md5', request()->route('task_class'))
            ->firstOrFail();

        $this->form->fill(
            [
                'task_class' => $taskClass->task_class,
                'title' => $taskClass->title,
                'description' => '',
                'risk' => Enums\TaskRisk::MEDIUM->value,
                'schedules_at' => null,
                'inputs' => collect($taskClass->task_class::getSupportInputs())
                    ->mapWithKeys(
                        static fn(Contracts\InputInterface $input): array => [
                            $input->getName() => [
                                'input_class' => \get_class($input),
                                'input_value' => null,
                                'is_file' => false,
                                'is_excluded' => false,
                            ]
                        ]
                    )
                    ->all(),
            ]
        );

        $this->callHook('afterFill');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getPreviewFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function handleRecordCreation(array $data): DatabaseTask
    {
        $inputs = $this->toInputs(Arr::pull($data, 'inputs'));

        /** @var DatabaseTask $taskModel */
        $taskModel = DatabaseTaskFacade::resolveModel(DatabaseTask::class, $data);

        $shouldValidate = \is_subclass_of($taskModel->toTask(), Contracts\ShouldValidate::class);

        $taskModel
            ->user()->associate(Auth::user())
            ->setAttribute('status', $shouldValidate ? Enums\TaskStatus::VALIDATING : Enums\TaskStatus::UNAPPLIED)
            ->save();

        $taskModel->saveInputs(...$inputs->all());

        $shouldValidate && Artisan::queue('task:dispatch-validate-job');

        return $taskModel;
    }

    protected function getPreviewFormAction(): Actions\Action
    {
        return Actions\Action::make('preview')
            ->label(__('database-task::model.database_task.actions.preview.label'))
            ->modalHidden(
                function (): bool {
                    try {
                        $this->form->validate();
                    } catch (ValidationException $e) {
                        $this->setErrorBag($e->validator->errors());

                        return true;
                    }

                    return false;
                }
            )
            ->modalContent(
                function (): Htmlable {
                    $data = $this->form->getState();

                    $inputs = $this->toInputs(Arr::pull($data, 'inputs'));

                    /** @var DatabaseTask $taskModel */
                    $taskModel = DatabaseTaskFacade::resolveModel(DatabaseTask::class, $data);

                    return $taskModel->toTask()?->preview(...$inputs->all());
                }
            )
            ->modalFooterActions([]);
    }

    protected function toInputs(array $inputs): Collection
    {
        return collect($inputs)
            ->map(static fn(array $array) => DatabaseTaskFacade::arrayToInput($array, 0))
            ->filter()
            ->values();
    }
}
