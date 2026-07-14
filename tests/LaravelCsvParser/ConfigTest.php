<?php

use App\Models\CsvFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use PHPTools\LaravelCsvParser\Jobs;

beforeEach()->with([fn() => __DIR__ . '/fixtures/users.csv']);

test('config auto_parse ON', function (string $file) {
    Config::set('csv-parser.auto_parse', true);

    Bus::fake();

    CsvFile::query()->create(['path' => $file]);

    Bus::assertChained(
        [
            Jobs\CollectCsvRowsJob::class,
            Jobs\ParseCsvRowsJob::class,
        ]
    );
});

test('config auto_parse OFF', function (string $file) {
    Config::set('csv-parser.auto_parse', false);

    Bus::fake();

    CsvFile::query()->create(['path' => $file]);

    Bus::assertNothingChained();
});
