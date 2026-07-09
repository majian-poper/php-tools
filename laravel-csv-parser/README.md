# Laravel CSV Parser

一个功能强大的 Laravel CSV 解析包，支持大文件处理、批量验证、分块处理和自动数据应用。

## 特性

- 🚀 **高性能**: 支持大文件分块处理，内存友好
- 📊 **灵活解析**: 自定义行解析器，支持复杂业务逻辑
- ✅ **数据验证**: 内置 Laravel 验证规则支持
- 🔄 **批量处理**: 支持批量验证和处理已验证的行
- 📝 **错误跟踪**: 详细的错误信息和行号记录
- 🎯 **事件驱动**: 完整的事件系统，便于监控和扩展
- 💾 **数据持久化**: 自动保存 CSV 数据和解析结果
- 🔧 **队列支持**: 异步处理大文件，避免超时

## 安装

```bash
composer require poper-tools/laravel-csv-parser
```

发布配置文件：

```bash
php artisan vendor:publish --provider="PHPTools\LaravelCsvParser\CsvParserPackageServiceProvider"
```

运行迁移：

```bash
php artisan migrate
```

## 核心概念

### 1. CsvFile (CSV 文件模型)

实现 `CsvFile` 接口的模型，代表一个 CSV 文件：

```php
use PHPTools\LaravelCsvParser\Contracts\CsvFile;
use PHPTools\LaravelCsvParser\Models\Concerns\HasCsvRows;

class ImportFile extends Model implements CsvFile
{
    use HasCsvRows;

    protected $fillable = ['path', 'name'];

    public function getSource(): CommaSeparatedValuesInterface
    {
        return new CommaSeparatedValues($this->path);
    }
}
```

### 2. RowParser (行解析器)

实现 `RowParser` 接口，定义如何解析每一行数据：

```php
use PHPTools\LaravelCsvParser\Contracts\RowParser;
use PHPTools\LaravelCsvParser\Contracts\RowParser\HasValidationRules;
use PHPTools\LaravelCsvParser\Contracts\HasUniqueKey;

class UserRowParser implements RowParser, HasValidationRules
{
    public function rules(array $headers): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ];
    }

    public function parse(array $row, int $no): \Generator
    {
        // User 需要实现 HasUniqueKey 接口
        $user = new User([
            'name' => $row['name'],
            'email' => $row['email'],
        ]);

        // orderNumber => result
        // Apply 时会按照 orderNumber / model_type 分组执行数据插入操作
        yield 1 => $user;
    }
}
```

## 使用方法

### 基本使用

```php
// 1. 创建 CSV 文件记录
$csvFile = ImportFile::create([
    'path' => '/path/to/users.csv',
    'name' => 'Users Import'
]);

// 2. 解析 CSV
$csvFile->parse(); // 异步处理

// 3. 将已经解析的内容反映到实际数据表中
$csvFile->apply(); // 异步处理
```

### 高级特性

#### 1. 批量处理

```php
use PHPTools\LaravelCsvParser\Contracts\RowParser\RowsHandler;

class BatchRowParser extends UserRowParser implements RowsHandler
{
    private array $existingEmails = [];

    public function handleRows(array $rows): array
    {
        // 在解析前对批量验证的行进行预处理
        $this->existingEmails = User::query()
            ->where('email', \array_unique(array_column($rows, 'email')))
            ->pluck('email')
            ->flip()
            ->toArray();

        return $rows;
    }

    public function parse(array $row, int $no): \Generator
    {
        // 使用预加载的数据进行快速检查
        if (isset($this->existingEmails[$row['email']])) {
            yield new MessageBag(['email' => 'Email already exists']);
        }

        // ...其他解析逻辑
    }
}
```

#### 2. 初始化支持

```php
use PHPTools\LaravelCsvParser\Contracts\RowParser\RequiresInitialization;

class InitializableRowParser extends UserRowParser implements RequiresInitialization
{
    protected $schools;

    public function initialize(CommaSeparatedValuesInterface $csv): void
    {
        // 预加载所有学校信息，优化性能
        $this->schools = School::query()
            ->pluck('id')
            ->flip()
            ->toArray();
    }

    public function parse(array $row, int $no): \Generator
    {
        // 使用预加载的数据进行快速检查
        if (isset($this->school[$row['email']])) {
            return;
        }

        // ... 其他解析逻辑
    }
}
```

#### 3. 复杂验证规则

```php
class AdvancedUserRowParser extends UserRowParser
{
    public function rules(array $headers): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                })
            ],
            'age' => 'nullable|integer|min:18|max:120',
            'department' => 'required|exists:departments,name',
        ];
    }
}
```

### 事件监听

```php
// 在 EventServiceProvider 中注册监听器
use PHPTools\LaravelCsvParser\Events;

protected $listen = [
    Events\CsvCollecting::class => [
        CollectingCsvListener::class,
    ],
    Events\CsvParsed::class => [
        CsvParsedListener::class,
    ],
    Events\ParsedCsvApplied::class => [
        SendNotificationListener::class,
    ],
];
```

```php
class CsvParsedListener
{
    public function handle(Events\CsvParsed $event): void
    {
        $file = $event->file;
        $totalRows = $file->rows()->count();
        $parsedResults = $file->parsed_rows()->count();

        Log::info("CSV parsing completed", [
            'file_id' => $file->id,
            'total_rows' => $totalRows,
            'parsed_results' => $parsedResults,
        ]);
    }
}
```

## 配置

配置文件 `config/csv-parser.php`：

```php
return [
    // 分块大小
    'chunk_size' => env('CSV_PARSER_CHUNK_SIZE', 100),

    // 是否在 CsvFile created 时自动解析
    'auto_parse' => env('CSV_PARSER_AUTO_PARSE', true),

    // 模型实现类
    'implementations' => [
        'csv_row' => \PHPTools\LaravelCsvParser\Models\CsvRow::class,
        'csv_parsed_row' => \PHPTools\LaravelCsvParser\Models\CsvParsedRow::class,
    ],
];
```

## API 参考

### CsvFile 接口方法

```php
// 解析 CSV（收集 + 解析)
$csvFile->parse();

// 仅应用已解析的数据
$csvFile->apply();

// 获取 CSV 源
$csvFile->getSource(); // 返回 CommaSeparatedValuesInterface

// 访问数据
$csvFile->header_row;    // 头行
$csvFile->content_rows;  // 内容行
$csvFile->parsed_rows;   // 解析结果
```

### 主要事件

- `CsvCollecting` / `CsvCollected` - CSV 收集阶段
- `CsvParsing` / `CsvParsed` - CSV 解析阶段
- `ParsedCsvApplying` / `ParsedCsvApplied` - 数据应用阶段
- `ParsedCsvRowsApplying` / `ParsedCsvRowsApplied` - 分组数据应用

## 最佳实践

### 1. 内存优化

```php
// 对于大文件，使用适当的分块大小
$parser = new CsvParser($rowParser, chunkSize: 50);

// 实现 RowsHandler 进行批量优化
class OptimizedRowParser implements RowsHandler
{
    public function handleRows(array $rows): array
    {
        // 批量查询相关数据，减少 SQL 查询次数
        $emails = array_column($rows, 'email');
        $existingUsers = User::whereIn('email', $emails)->get()->keyBy('email');

        // 将查询结果缓存到解析器中使用
        $this->existingUsers = $existingUsers;

        // 此处也可以对 $rows 做一些修改, 如去除重复行
        // 或者向每行 $row 增加一下中间值, 可在后续 parse 中使用
        return $rows;
    }
}
```

### 2. 错误处理

```php
public function parse(array $row, int $no): \Generator
{
    try {
        // 解析逻辑
        $user = $this->createUser($row);
        yield 1 => $user;
    } catch (\Exception $e) {
        // 返回错误信息
        yield new MessageBag(['error' => $e->getMessage()]);
    }
}
```

## 测试

```bash
# 运行测试
./vendor/bin/pest
```
