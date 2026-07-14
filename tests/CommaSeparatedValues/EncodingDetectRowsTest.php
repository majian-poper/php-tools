<?php

declare(strict_types=1);

use PHPTools\CommaSeparatedValues\CommaSeparatedValues;

describe('CommaSeparatedValues for Shift_JIS csv', function () {
    beforeEach()->with([fn() => makeCsv(__DIR__ . '/fixtures/shiftjis-detect-rows.csv', [CommaSeparatedValues::OPTION_ENCODING_LIST => ['SJIS-win']])]);

    test('getEncoding returns Shift_JIS', function (CommaSeparatedValues $csv) {
        expect($csv->getEncoding())->toBe('SJIS-win');
    });

    test('getEncoding returns incorrect UTF-8 encoding', function (CommaSeparatedValues $csv) {
        expect($csv->setOptions([CommaSeparatedValues::OPTION_DETECT_ENCODING_ROWS => 1])->getEncoding())->toBe('UTF-8');
    });
});
