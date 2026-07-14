<?php

declare(strict_types=1);

use PHPTools\CommaSeparatedValues\CommaSeparatedValues;

describe('CommaSeparatedValues for UTF-8 csv', function () {
    beforeEach()->with([fn() => makeCsv(__DIR__ . '/fixtures/utf8.csv')]);

    test('getBasename returns correct basename', function (CommaSeparatedValues $csv) {
        expect($csv->getBasename())->toBe(pathinfo(__DIR__ . '/fixtures/utf8.csv', PATHINFO_BASENAME));
    });

    test('withBom returns false', function (CommaSeparatedValues $csv) {
        expect($csv->withBom())->toBeFalse();
    });

    test('getEncoding returns UTF-8', function (CommaSeparatedValues $csv) {
        expect($csv->getEncoding())->toBe('UTF-8');
    });

    test('getHeaders returns correct headers', function (CommaSeparatedValues $csv) {
        expect($csv->getHeaders())->toBe(['id', 'name', 'city', 'score', 'remark']);
    });

    test('readRow returns correct first row', function (CommaSeparatedValues $csv) {
        $firstRow = $csv->readRow()->current();

        expect($firstRow)->toBe(['id' => '1', 'name' => 'Alice', 'city' => 'Tokyo', 'score' => '88', 'remark' => 'Good']);
    });

    test('readRows returns correct rows', function (CommaSeparatedValues $csv) {
        $rows = $csv->readRows(3)->current();
        expect(count($rows))->toBe(3);

        $rows = $csv->readRows(10)->current();
        expect(count($rows))->toBe(5);
    });

    test('readRow returns correct row number', function (CommaSeparatedValues $csv) {
        $no = 1;

        foreach ($csv->readRow([CommaSeparatedValues::OPTION_WITH_HEADER => false]) as $rowNumber => $_) {
            expect($rowNumber)->toBe($no++);
        }
    });

    test('readRows returns correct row number', function (CommaSeparatedValues $csv) {
        $no = 1;

        foreach ($csv->readRows(5, [CommaSeparatedValues::OPTION_WITH_HEADER => false]) as $rows) {
            foreach ($rows as $rowNumber => $_) {
                expect($rowNumber)->toBe($no++);
            }
        }
    });
});
