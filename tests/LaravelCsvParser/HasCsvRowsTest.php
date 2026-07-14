<?php

use App\Models\CsvFile;

beforeEach()->with([fn() => CsvFile::query()->create(['path' => __DIR__ . '/fixtures/users.csv'])]);

it('works like csv file', function (CsvFile $csvFile) {
    expect($csvFile->getHeaders())->toBe(['id', 'name', 'email_address']);

    expect($csvFile->readRow()->current())
        ->toBe(['id' => '1', 'name' => 'John Doe', 'email_address' => 'john@example.com']);
});

test('deleting csv file also deletes related rows and parsed rows', function (CsvFile $csvFile) {
    $csvFile->parse();

    expect($csvFile->rows()->count())->toBeGreaterThan(0);
    expect($csvFile->parsed_rows()->count())->toBeGreaterThan(0);

    $csvFile->delete();

    expect($csvFile->rows()->count())->toBe(0);
    expect($csvFile->parsed_rows()->count())->toBe(0);
});

test('readRows chunks rows and yields remainder', function (CsvFile $csvFile) {
    /** @var array $chunks */
    $chunks = iterator_to_array($csvFile->readRows(2));

    expect($chunks)->toHaveCount(3); // 5 data rows -> 2,2,1
    expect(array_keys($chunks))->toBe([0, 1, 2]);
    expect(array_keys($chunks[0]))->toBe([2, 3]);
    expect(array_keys($chunks[1]))->toBe([4, 5]);
    expect(array_keys($chunks[2]))->toBe([6]);

    expect($chunks[2][6])
        ->toBe(['id' => '5', 'name' => 'Michael Brown', 'email_address' => 'michael@example.com']);
});
