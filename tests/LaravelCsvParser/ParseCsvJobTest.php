<?php

use App\Models\CsvFile;
use App\Models\User;
use App\Support\UserRowParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\MessageBag;
use PHPTools\CommaSeparatedValues\Contracts\CommaSeparatedValuesInterface;
use PHPTools\LaravelCsvParser\Contracts\RowParser;
use PHPTools\LaravelCsvParser\Events;

beforeEach(fn() => config()->set('csv-parser.auto_parse', true))
    ->with([fn() => CsvFile::query()->create(['path' => __DIR__ . '/fixtures/users.csv'])]);

test('fired CsvCollect event', function (CsvFile $csvFile) {
    Event::fake();

    $csvFile->parse();

    Event::assertDispatched(Events\CsvCollecting::class);
    Event::assertDispatched(Events\CsvCollected::class);
    Event::assertDispatched(Events\CsvParsing::class);
    Event::assertDispatched(Events\CsvParsed::class);
});

test('csv (parsed) rows inserted', function (CsvFile $csvFile) {
    $alice = User::query()->create([
        'name' => 'Alice Smith',
        'email' => 'alice@example.com',
        'password' => 'password',
    ]);

    $csvFile->parse();

    expect($csvFile->rows()->count())->toBe(6);
    expect($csvFile->parsed_rows()->count())->toBe(5);

    expect($csvFile->header_row->content)->toBe(['id', 'name', 'email_address']);

    $firstContentRow = $csvFile->content_rows->first();

    expect($firstContentRow->no)->toBe(2);
    expect($firstContentRow->content)->toBe(['1', 'John Doe', 'john@example.com']);

    $firstParsedRow = $csvFile->parsed_rows()->first();

    expect($firstParsedRow->no)->toBe(2);
    expect($firstParsedRow->model_type)->toBe(App\Models\User::class);
    expect(Arr::only($firstParsedRow->values, ['email', 'name']))->toBe([
        'email' => 'john@example.com',
        'name' => 'John Doe',
    ]);

    expect(Hash::check('password', $firstParsedRow->values['password']))->toBeTrue();

    $aliceParsedRow = $csvFile->parsed_rows()->where('values->email', 'alice@example.com')->first();

    expect($aliceParsedRow->no)->toBe(5);
    expect($aliceParsedRow->model->getKey())->toBe($alice->getKey());
});

test('error messages stored when row parser validation failures', function (CsvFile $csvFile) {
    $csvFile = new class extends CsvFile
    {
        protected $table = 'csv_files';

        public function getRowParser(): RowParser
        {
            return new class extends UserRowParser implements RowParser\HasValidationRules
            {
                public function rules(CommaSeparatedValuesInterface $csv): array
                {
                    return [
                        'name' => ['required', 'string', 'max:255'],
                        'email_address' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    ];
                }
            };
        }
    };

    $csvFile->path = __DIR__ . '/fixtures/users_invalid.csv';
    $csvFile->save();

    $csvFile->parse();

    $parsedRows = $csvFile->parsed_rows()->get();

    expect($parsedRows)->toHaveCount(2);

    $validationErrorRow = $parsedRows->firstWhere('no', 2);

    expect($validationErrorRow->errors)->toHaveKeys(['name', 'email_address']);
    expect($validationErrorRow->values)->toBeEmpty();
    expect($validationErrorRow->order_number)->toBe(0);
});

test('message bag stored when row parser yields errors', function (CsvFile $csvFile) {
    $csvFile = new class extends CsvFile
    {
        protected $table = 'csv_files';

        public function getRowParser(): RowParser
        {
            return new class extends UserRowParser
            {
                public function parse(array $row, int $no): \Generator
                {
                    yield new MessageBag(['error' => 'yield error message']);
                }
            };
        }
    };

    $csvFile->path = __DIR__ . '/fixtures/users.csv';
    $csvFile->save();

    $csvFile->parse();

    $parsedRows = $csvFile->parsed_rows()->get();

    $parsedRows->each(fn($row) => expect($row->errors)->toBe(['error' => ['yield error message']]));
});

test('message bag stored when row parser throw exceptions', function () {
    $csvFile = new class extends CsvFile
    {
        protected $table = 'csv_files';

        public function getRowParser(): RowParser
        {
            return new class extends UserRowParser
            {
                public function parse(array $row, int $no): \Generator
                {
                    throw new \Exception('throw error message');
                }
            };
        }
    };

    $csvFile->path = __DIR__ . '/fixtures/users.csv';
    $csvFile->save();

    $csvFile->parse();

    $parsedRows = $csvFile->parsed_rows()->get();

    $parsedRows->each(fn($row) => expect($row->errors)->toBe([$row->no => ['throw error message']]));
});

test('aligns existing target rows when numbers are out of sync', function (CsvFile $csvFile) {
    $csvFile->rows()->delete(); // clear rows written by auto-parse on CsvFile creation

    $legacy = $csvFile->rows()->create(['no' => 0, 'content' => ['legacy']]);
    $header = $csvFile->rows()->create(['no' => 1, 'content' => ['id', 'name', 'email_address']]);
    $stale = $csvFile->rows()->create(['no' => 3, 'content' => ['2', 'Old Name', 'old@example.com']]);

    $csvFile->parse();

    $rows = $csvFile->rows()->orderBy('no')->get();

    expect($rows)->toHaveCount(7);

    expect($rows->firstWhere('no', 0)->getKey())->toBe($legacy->getKey()); // key < $no, skips it

    $headerRow = $rows->firstWhere('no', 1);
    expect($headerRow->getKey())->toBe($header->getKey()); // key === $no
    expect($headerRow->content)->toBe(['id', 'name', 'email_address']);

    $secondRow = $rows->firstWhere('no', 2);
    expect($secondRow->content)->toBe(['1', 'John Doe', 'john@example.com']); // insert after key > $no branch

    $updatedRow = $rows->firstWhere('no', 3);
    expect($updatedRow->getKey())->toBe($stale->getKey()); // reused existing id
    expect($updatedRow->content)->toBe(['2', 'Jane Smith', 'jane@example.com']);
});
