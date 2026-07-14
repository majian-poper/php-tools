<?php

declare(strict_types=1);

use PHPTools\CommaSeparatedValues\CommaSeparatedValues;

describe('CommaSeparatedValues for multi-encoding csv', function () {
    beforeEach()->with([fn() => makeCsv(__DIR__ . '/fixtures/multi-encoding.csv')]);

    test('getEncoding throws exception', function (CommaSeparatedValues $csv) {
        $csv->getEncoding();
    })->throws(RuntimeException::class);
});
