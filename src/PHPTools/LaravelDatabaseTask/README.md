# Laravel Database Task

持久化数据库任务的输入、执行状态和输出，提供异步验证、批处理与结果合并，并通过 Filament 展示任务和下载文件。

## 安装

环境要求：PHP `^8.4`；Filament `^3.0 || ^4.0 || ^5.0`。使用主包时 Illuminate 版本约束为 `^12.0 || ^13.0`。

本仓库的主包包含该组件，并通过 Composer `replace` 声明提供 `php-tools/laravel-database-task`：

```bash
composer require php-tools/packages:^1.0.2
php artisan vendor:publish --tag=database-task-config
php artisan vendor:publish --tag=database-task-migrations
php artisan migrate
```

独立发布该组件的环境也可使用 `php-tools/laravel-database-task`。ServiceProvider 通过 Composer 自动发现。生产环境不会自动加载 vendor migration，必须先发布；发布 tag 是 `database-task-migrations`。

文件输入输出依赖 Spatie Media Library，应用须配置其 media 表、磁盘和访问权限。队列批处理依赖 `job_batches` 表，以及可用的 queue worker 和 cache lock 存储。请先完成这些依赖在应用中的初始化，不要将本包的任务表迁移当作它们的初始化。

在 Filament Panel Provider 注册插件：

```php
use Filament\Panel;
use PHPTools\LaravelDatabaseTask\DatabaseTaskPlugin;

public function panel(Panel $panel): Panel
{
    return $panel->plugins([
        DatabaseTaskPlugin::make()->navigationSort(90),
    ]);
}
```

插件注册任务类型和任务记录两个资源。访问控制由应用的认证、policy 和 panel 配置负责。

## 配置

`config/database-task.php`：

| 配置 | 默认值 | 用途 |
| --- | --- | --- |
| `tasks.classes` | `[]` | 注册实现 `TaskInterface` 的具体任务类 |
| `implementations` | 包内模型 | 替换 task、input、output、file、task class 模型 |
| `queue.timeout` | `60` | Job 默认超时秒数，也是 command lock 的有效期 |
| `queue.delay` | `3` | 后续 dispatch command 与 merge Job 的默认延迟秒数 |
| `queue.tries` | `3` | Job 默认尝试次数 |

例如注册 `App\DatabaseTask\ExampleTask::class`：

```php
'tasks' => [
    'classes' => [\App\DatabaseTask\ExampleTask::class],
],
```

任务目录不是自动扫描注册。具体任务类应能无参实例化，因为任务类型列表使用 `new $taskClass` 读取标题。

## 状态流程

| 状态 | 含义 |
| --- | --- |
| `CREATED` | 已创建，等待验证 command 领取 |
| `VALIDATING` | 已领取，验证进行中 |
| `VALIDATED` | 验证通过，或无需验证；可以提交 |
| `REQUESTED` | 已提交，等待应用完成审批或其他授权流程 |
| `READY` | 已授权，等待 process command 领取 |
| `PROCESSING` | 处理中；批任务的 merge 也在此阶段 |
| `PROCESSED` | 输出已保存，处理完成 |
| `FAILED` | 验证、派发或处理失败 |

```text
CREATED -> VALIDATING -> VALIDATED -> REQUESTED -> READY -> PROCESSING -> PROCESSED
               |                                               |
               +-------------------> FAILED <------------------+
```

Filament 创建页对实现 `ShouldValidate` 的任务使用 `CREATED` 并排队验证 command；其他任务直接使用 `VALIDATED`。验证 command 也能把不要求验证的 `CREATED` 任务推进到 `VALIDATED`。

模型状态方法有来源条件：`toValidating()`、`toValidated()`、`toRequested()`、`toReady()`、`toProcessing()`、`toProcessed($output)`。返回 `false` 时不能继续假定状态转换成功。`toFailed($reason)` 保存失败原因；`changeStatus($to, $from = null)` 是底层状态更新入口。

### 提交与审批

包不自带审批决策。Filament 的提交操作调用 `toRequested()`，成功后派发 `TaskRequested($databaseTask, $user)`。应用可监听该事件创建 ApprovalTask，或执行自己的授权逻辑；审批通过后调用 `toReady()` 并排队 process command。

`toRequested()` 本身只改变状态，不自动发送 `TaskRequested`。直接调用模型的应用需自行派发该事件。审批拒绝的处理策略也由应用决定；不要在枚举中继续使用已移除的 `APPROVED` / `REJECTED`。

### 调度命令

```bash
php artisan task:dispatch-validate-job
php artisan task:dispatch-process-job
```

验证命令查找有输入的 `CREATED` 任务，条件更新为 `VALIDATING` 后派发 batch。处理命令查找有输入、`schedules_at` 为空或已到期的 `READY` 任务，条件更新为 `PROCESSING` 后派发 batch。仍有候选任务时，命令会按 `queue.delay` 排队自身。

建议调度两条命令作为恢复入口，例如在应用 scheduler 中分别 `everyMinute()`；同时运行实际 queue worker。未来计划时间不会让当前 command 等待到点，应由 scheduler 在到期后重新扫描。条件更新与锁用于认领，但不等于业务副作用的 exactly-once 保证；worker 崩溃、入队失败和手动重试需要应用自己的恢复策略。

## 实现普通任务

```php
namespace App\DatabaseTask;

use Illuminate\Support\Collection;
use PHPTools\LaravelDatabaseTask\Concerns\Input\AsNumber;
use PHPTools\LaravelDatabaseTask\Concerns\InteractsWithTask;
use PHPTools\LaravelDatabaseTask\Contracts\InputInterface;
use PHPTools\LaravelDatabaseTask\Contracts\OutputInterface;
use PHPTools\LaravelDatabaseTask\Contracts\TaskInterface;
use PHPTools\LaravelDatabaseTask\Outputs\TextOutput;

class CountInput implements InputInterface
{
    use AsNumber;

    public function getLabel(): string
    {
        return 'Count';
    }
}

class ExampleTask implements TaskInterface
{
    use InteractsWithTask;

    public static function getSupportInputs(): array
    {
        return [(new CountInput)->required()];
    }

    public function getTitle(): string
    {
        return 'Example task';
    }

    protected function handlePreview(Collection $filteredInputs): string
    {
        return 'Example preview';
    }

    protected function handleRun(Collection $filteredInputs): OutputInterface
    {
        return new TextOutput('Count: ' . $filteredInputs->get('count_input')->getValue());
    }
}
```

将 Input 和 Task 分别保存到对应的 PSR-4 文件中，再注册任务类。默认两个 command 使用 `whereHas('inputs')`，所以通过默认 dispatcher 执行的任务须保存至少一个输入。

`InteractsWithTask` 把输入按 `getName()` 组织成 Collection，再交给 `handlePreview()` / `handleRun()`。自定义 Input 实现 `InputInterface`，可组合 `Concerns\Input\AsNumber`、`AsQuery`、`AsBoolean`、`AsSelect` 或 `AsDateTime`；文件类可继承 `Inputs\FileInput`。

默认输入名称是类短名的 snake_case，例如 `SchoolId` 为 `school_id`。默认 label 来自 `database-task::tasks.inputs.school_id.label`；需要不同名称或标签时重写 `getName()` / `getLabel()`，没有通用 `name()` / `label()` setter。

`value($value)` 只能设置一次非 null 值；读取使用 `getValue()`。普通值持久化时会转为字符串，iterable 以逗号连接，不自动保证原始类型往返；依赖整数、日期或多选数组的应用应在 Input / task 中明确处理类型。

### 验证

任务额外实现 `Contracts\ShouldValidate`；使用该 trait 时实现 `handleValidate(Collection $filteredInputs): bool`。返回 true 标记本批输入的 `validated_at`；返回 false 或抛出异常进入失败路径。批次结束后，没有未验证输入且状态仍可推进时进入 `VALIDATED`。

### 批处理与合并

任务实现 `Contracts\BatchableTask`，并通过 trait 的两个 hook 提供批输入和合并：

- `handleGetBatchableInputs(Collection $filteredInputs): iterable`：生成实现 `BatchableInput` 的输入，每批使用正数 `batchOrder`。
- `handleMergeBatchableOutputs(Collection $batchableOutputs): OutputInterface`：返回最终输出，若输出实现 `BatchableOutput`，最终 `batchOrder` 必须为 0。

`Inputs\ChunkInput` 已实现 `BatchableInput`，可用 `(new ChunkInput)->value(100)->batchOrder(1)` 表示应用自行解释的分块参数。批次 0 是共享输入；有正数批次时不单独派发 batch 0，但每个 Job 都读取共享输入和当前批输入。同名输入由较高批次的输入覆盖共享输入。`saveInputs()` 在保存时生成批输入，不是在执行时生成。

`ProcessTask` 为输出设置当前 `batchOrder`，保存每批输出；处理 batch 完成后 dispatch `MergeTask`。Trait 在合并前按批次排序输出。使用 `TextOutput`、`ArrayOutput`、`FileOutput` 均可支持批输出，不再需要旧 `BatchableTextOutput` / `BatchableFileOutput`。

## 文件与输出

- `TextOutput('text')`：文本结果。
- `ArrayOutput(['count' => 10])`：JSON 结果，恢复后用 `getArray()` 读取。
- `FileOutput($filename, $mode)`：包装文件；默认 mode 为 `w+`，读取既有文件须显式传入 `r`，避免截断。
- `FileInput`：接受 `TemporaryUploadedFile`、`SplFileObject` 或 stream；`getValue()` 返回缓存的 `SplFileObject`，`getStream()` 返回可读 stream。

```php
use PHPTools\LaravelDatabaseTask\Outputs\FileOutput;

$output = new FileOutput($temporaryPath, 'w+');
$output->fputcsv(['id', 'name'], ',', '"', '\\');
$output->fputcsv([1, 'Example'], ',', '"', '\\');
```

文件方法通过代理调用底层 `SplFileObject`，`FileOutput` 本身不再继承 `SplFileObject`。保存模型时文件转存为 Media；读取持久化输入输出时从 Media stream 恢复文件。

文件对象默认 `autoClean(true)`，析构可能删除已缓存且可写的底层文件，包括应用提供的路径。需要继续持有源文件时显式使用 `autoClean(false)` 并自行清理。stream 物化时会关闭输入 stream；不要假定调用后仍可复用原句柄。输出可用 `expiresAt()` 设置有效期，下载是否允许仍须结合应用权限和实际存储配置确认。

### 超时与延迟

任务可提供 `timeout()`、`tries()` 覆盖 Job 默认值；实现 `Contracts\DelayableTask::delay(int $batchOrder = 0): int` 可指定每批入队延迟秒数。延迟并不保证执行顺序，多 worker 的启动和完成顺序可能不同。

## 事件

所有事件位于 `PHPTools\LaravelDatabaseTask\Events`，公共构造参数如下：

| 事件 | 参数 |
| --- | --- |
| `TaskRequested` | DatabaseTask、nullable Authenticatable |
| `TaskDispatching` / `TaskDispatched` | DatabaseTask |
| `TaskDispatchFailed` | DatabaseTask、Throwable |
| `TaskValidating` / `TaskValidated` | DatabaseTask、int batchOrder |
| `TaskValidateFailed` | DatabaseTask、int batchOrder、Throwable |
| `TaskProcessing` / `TaskProcessed` | DatabaseTask、int batchOrder |
| `TaskProcessFailed` | DatabaseTask、int batchOrder、Throwable |
| `TaskMerging` / `TaskMerged` | DatabaseTask |
| `TaskMergeFailed` | DatabaseTask、Throwable |
| `TaskMediaDownloading` | DatabaseTaskInput 或 DatabaseTaskOutput、nullable Authenticatable |

Dispatch 事件用于 process command 的派发阶段；`TaskDispatched` 不表示执行完成。验证 command 的整体阶段事件使用 batchOrder 0，与单个批次事件需在 listener 中结合上下文区分。监听器自身的异常会影响调用路径，避免在同步 listener 中做不可控的外部 IO。

## 从 1.0.1 升级

1. 更新 task 接口：`BatchableTaskInterface` 改为 `BatchableTask`；原独立的 batch task / stream / output traits 及多个旧 Job 类已移除。使用当前 trait hooks 和输出类。
2. 更新调用方法：Manager / Facade 的 `fromInputArray()`、`fromInput()`、`fromOutput()` 分别迁移至当前 `arrayToInput()` / `arrayToInputModel()`、`toInputModel()`、`toOutputModel()`；模型改为 `changeStatus()` 和 `to*()` 方法，原 `request()` 已移除。
3. 更新 scheduler / listener：旧 `approved-task:dispatch` 换成 `task:dispatch-process-job`，按需增加验证 command；审批通过后的状态现在是 `READY`，申请中是 `REQUESTED`，可提交状态是 `VALIDATED`。删除策略和所有旧 enum 引用须一并更新。
4. 更新事件监听器：使用本节事件表中的新类名和参数，不再监听旧 `TaskRunning` / `TaskRunFinished` 或旧 `BatchableTask*` 事件。
5. 发布并检查 migrations。包内已有 `000002_add_batch_columns`；`000003_add_validated_at_columns` 增加验证时间，并把旧值 `unapplied -> validated`、`pending -> requested`、`approved -> ready`、`rejected -> failed`。
6. 注意：上述旧状态映射位于“缺少 validated_at 列”分支内。如果应用已提前加了该列，映射不会执行；已有旧状态值需应用另行迁移后再启用新 enum cast。此 migration 的 `down()` 不恢复旧值。历史审批 JSON 中保存的状态或模型类名也需要应用检查。
7. 维护窗口内处理旧队列、进行中的 batch 和状态记录。旧序列化 Job 类名、已删除的 input/output 类名不能由新版本自动恢复。备份数据库并制定恢复策略，不在仍运行旧 worker 时直接混用新状态。
8. 已发布配置和翻译不会自动刷新；合并新 keys，而不是直接覆盖应用定制文件。发布翻译的 tag 是 `database-task-translations`。

## 测试与当前限制

在仓库根目录运行 `./vendor/bin/pest`；当前测试覆盖 CSV、Approval、CSV parser 和任务文件 IO，不包含完整的验证/处理状态机、Filament 浏览器流程或迁移升级场景。

许可证：MIT，见仓库根目录的 [LICENSE.md](../../../LICENSE.md)。