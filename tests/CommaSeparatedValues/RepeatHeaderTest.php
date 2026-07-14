<?php

declare(strict_types=1);

use PHPTools\CommaSeparatedValues\CommaSeparatedValues;

describe('CommaSeparatedValues with repeated header', function () {
    beforeEach()->with([fn() => makeCsv(__DIR__ . '/fixtures/repeat-header.csv')]);

    test('readRow returns correct first row with repeated header file', function (CommaSeparatedValues $csv) {
        $headers = $csv->getHeaders();

        expect($headers)->toBe(['id', 'name', 'city', 'score', 'remark', 'score (2)', 'remark (2)', 'score (3)']);
    });
});
