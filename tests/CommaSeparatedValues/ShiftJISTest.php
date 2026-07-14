<?php

declare(strict_types=1);

use PHPTools\CommaSeparatedValues\CommaSeparatedValues;

describe('CommaSeparatedValues for Shift_JIS csv', function () {
    beforeEach()->with([fn() => makeCsv(__DIR__ . '/fixtures/shiftjis.csv', [CommaSeparatedValues::OPTION_ENCODING_LIST => ['SJIS-win']])]);

    test('getBasename returns correct basename', function (CommaSeparatedValues $csv) {
        expect($csv->getBasename())->toBe(pathinfo(__DIR__ . '/fixtures/shiftjis.csv', PATHINFO_BASENAME));
    });

    test('withBom returns false', function (CommaSeparatedValues $csv) {
        expect($csv->withBom())->toBeFalse();
    });

    test('getEncoding returns Shift_JIS', function (CommaSeparatedValues $csv) {
        expect($csv->getEncoding())->toBe('SJIS-win');
    });

    test('getHeaders returns correct headers', function (CommaSeparatedValues $csv) {
        expect($csv->getHeaders())->toBe(['番号', '氏名', '都市', '点数', '備考']);
    });

    test('readRow returns correct first row', function (CommaSeparatedValues $csv) {
        $firstRow = $csv->readRow()->current();

        expect($firstRow)->toBe(['番号' => '1', '氏名' => '山田太郎', '都市' => '東京', '点数' => '90', '備考' => '優秀']);
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
