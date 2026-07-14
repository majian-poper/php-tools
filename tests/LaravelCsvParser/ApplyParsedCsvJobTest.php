<?php

use App\Models\ContactCsvFile;
use App\Models\CsvFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPTools\LaravelCsvParser\Events;
use PHPTools\LaravelCsvParser\Models\CsvParsedRow;

beforeEach(fn() => config()->set('csv-parser.auto_parse', true))
    ->with([fn() => CsvFile::query()->create(['path' => __DIR__ . '/fixtures/users.csv'])]);

test('fired ApplyParsedCsv event', function (CsvFile $csvFile) {
    Event::fake();

    $csvFile->apply();

    Event::assertDispatched(Events\ParsedCsvApplying::class);
    Event::assertDispatched(Events\ParsedCsvApplied::class);
    Event::assertDispatched(Events\ParsedCsvRowsApplying::class);
    Event::assertDispatched(Events\ParsedCsvRowsApplied::class);
});

test('dirty parsed rows are upserted with model ids', function (CsvFile $csvFile) {
    CsvParsedRow::retrieved(fn(CsvParsedRow $row) => $row->file_id === $csvFile->getKey() && $row->errors = '{}');

    $queries = [];
    DB::listen(static function ($query) use (&$queries): void {
        $queries[] = strtolower($query->sql);
    });

    $csvFile->apply();

    $upserted = collect($queries)->contains(fn(string $sql) => str_contains($sql, 'insert into "csv_parsed_rows"') && str_contains($sql, 'on conflict ("id") do update'));

    expect($upserted)->toBeTrue();

    CsvParsedRow::flushEventListeners();
});

test('model id remain same when unique keys collide across model types', function (CsvFile $csvFile) {
    $csvFile->apply();

    $uniqueKey = 'john@example.com';

    $originalParsedRow = CsvParsedRow::query()
        ->where('file_type', $csvFile->getMorphClass())
        ->where('file_id', $csvFile->getKey())
        ->where('model_unique_key', $uniqueKey)
        ->first();

    $originalModelId = $originalParsedRow->model_id;

    $anotherCsvFile = ContactCsvFile::query()->create([
        'path' => __DIR__ . '/fixtures/contacts.csv',
    ]);

    $anotherCsvFile->apply();

    $originalParsedRow->refresh();

    expect($originalParsedRow->model_id)->toBe($originalModelId);
});
