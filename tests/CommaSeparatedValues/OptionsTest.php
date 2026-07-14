<?php

declare(strict_types=1);

use PHPTools\CommaSeparatedValues\CommaSeparatedValues;

describe('CommaSeparatedValues with options', function () {
    beforeEach()->with([fn() => makeCsv(__DIR__ . '/fixtures/options.csv')]);

    test('with header option OFF returns correct first row', function (CommaSeparatedValues $csv) {
        $firstRow = $csv->readRow([CommaSeparatedValues::OPTION_WITH_HEADER => false])->current();

        expect($firstRow)->toBe(['id', 'name', 'city', 'score', 'remark']);
    });

    test('trim option OFF returns correct first row', function (CommaSeparatedValues $csv) {
        $firstRow = $csv->readRow([CommaSeparatedValues::OPTION_TRIM => false])->current();

        expect($firstRow)->toBe(['id' => '1', 'name' => ' Alice', 'city' => ' Tokyo ', 'score' => '88 ', 'remark' => null]);
    });

    test('empty to null option OFF returns correct first row', function (CommaSeparatedValues $csv) {
        $firstRow = $csv->readRow([CommaSeparatedValues::OPTION_EMPTY_TO_NULL => false])->current();

        expect($firstRow)->toBe(['id' => '1', 'name' => 'Alice', 'city' => 'Tokyo', 'score' => '88', 'remark' => '']);
    });

    test('skip empty row option ON returns correct row number', function (CommaSeparatedValues $csv) {
        $options = [CommaSeparatedValues::OPTION_WITH_HEADER => false];

        $rows = \iterator_to_array($csv->readRow($options));

        expect(\array_keys($rows))->toBe([1, 2, 3, 4, 6]);
    });

    test('skip empty row option OFF returns correct row number', function (CommaSeparatedValues $csv) {
        $options = [
            CommaSeparatedValues::OPTION_WITH_HEADER => false,
            CommaSeparatedValues::OPTION_SKIP_EMPTY_ROW => false,
        ];

        $rows = \iterator_to_array($csv->readRow($options));

        expect(\array_keys($rows))->toBe([1, 2, 3, 4, 5, 6]);
    });
});
