# Filament 审批扩展 (Filament Approval)

这是 `php-tools/laravel-approval` 的 Filament 集成包。该扩展提供了一套 Filament 资源，用于管理审批流以及查看和处理审批任务。

## 安装

通过 composer 安装：

```bash
composer require php-tools/laravel-filament-approval
```

## 使用方法

### 插件注册

在你的 Filament Panel Provider 中添加 `FilamentApprovalPlugin`：

```php
use PHPTools\LaravelFilamentApproval\FilamentApprovalPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugins([
            FilamentApprovalPlugin::make()
        ]);
}
```

### 配置与初始化

在使用之前，你需要发布并根据需求修改配置文件：

```bash
php artisan vendor:publish --tag="filament-approval-config"
```

### 配置文件说明

配置文件允许你自定义以下内容：

-   **可审批模型 (Approvable Models)**：定义哪些模型需要进入审批流程。
    -   键名 (Key)：模型的类名。
    -   键值 (Value)：对应显示的 Label。
-   **审批者模型 (Approver Models)**：可以在审批流步骤中被选为审批者的模型列表（如：角色、账号）。
-   **失效天数选项 (Expiration Days)**：在创建审批流时，可供选择的过期天数选项（以天为单位）。
-   **导航 (Navigation)**：自定义资源在侧边栏的排序和图标。

```php
// config/laravel-filament-approval.php 示例

return [
    // ...
    'approvable_models' => [
        // 示例：左侧为模型类，右侧为 Label
        // \App\Models\Post::class => 'Post',
    ],

    'approver_models' => [
        \App\Models\AccountManagement\Role::class => 'Role',
        \App\Models\AccountManagement\Account::class => 'Account',
    ],

    'expiration_days' => [1, 2, 3, 4, 5, 6, 7], // 审批流可选的过期天数
];
```

## 包含的资源

该插件会自动注册以下资源：

-   **审批流管理 (ApprovalFlowResource)**：管理审批流程、步骤和规则。
-   **审批任务 (ApprovalTaskResource)**：查看、追踪和处理审批任务的状态。

## 自定义

### 禁用审批流管理

如果你在某些面板（如用户面板）只需要展示审批任务而不需要管理流程，可以禁用审批流资源：

```php
FilamentApprovalPlugin::make()
    ->withoutApprovalFlows()
```

### 审批人权限控制

默认情况下，当前登录用户被视为唯一的审批人。你可以通过 `resolveApproversUsing` 来扩展审批权限（例如允许用户以“角色”身份进行审批）：

```php
FilamentApprovalPlugin::make()
    ->resolveApproversUsing(function ($user) {
        // 返回可以进行审批的实体数组
        return [$user, ...$user->roles];
    })
```

### 自定义任务列表 Tabs

你可以通过 `hasTabs` 方法自定义审批任务列表页的选项卡（Tabs），例如按状态筛选任务：

```php
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

FilamentApprovalPlugin::make()
    ->hasTabs([
        'all' => Tab::make('全部'),
        'pending' => Tab::make('待审批')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
    ])
```
