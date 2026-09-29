<?php

declare(strict_types=1);

use PHPTools\CommaSeparatedValues\CommaSeparatedValues as CSV;

describe('CSV with offset, limit combinations', function () {
    beforeEach()->with([fn() => makeCsv(__DIR__ . '/fixtures/offset-limit.csv')]);

    describe('readRow skip empty row', function () {
        test('offset 0, limit 0 - returns all data rows', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 0
            ]));

            expect(\count($rows))->toBe(10);
            expect(\array_keys($rows))->toBe([2, 3, 5, 7, 8, 10, 11, 13, 14, 15]);
        });

        test('offset 0, limit 1 - returns first data row only', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 1,
            ]));

            expect(\count($rows))->toBe(1);
            expect(\array_keys($rows))->toBe([2]);
            $firstRow = \array_values($rows)[0];
            expect($firstRow)->toBe(['id' => '1', 'name' => 'Alice', 'department' => 'Engineering', 'salary' => '75000', 'status' => 'Active']);
        });

        test('offset 1, limit 0 - skips first data row, returns rest', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 1,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\count($rows))->toBe(9);
            expect(\array_keys($rows))->toBe([3, 5, 7, 8, 10, 11, 13, 14, 15]);
        });

        test('offset 1, limit 1 - skips first, returns second data row only', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 1,
                CSV::OPTION_LIMIT => 1,
            ]));

            expect(\count($rows))->toBe(1);
            expect(\array_keys($rows))->toBe([3]);
            $firstRow = \array_values($rows)[0];
            expect($firstRow)->toBe(['id' => '2', 'name' => 'Bob', 'department' => 'Marketing', 'salary' => '65000', 'status' => 'Active']);
        });

        test('offset 15 (exceeds total) - returns empty', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 15,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\count($rows))->toBe(0);
        });
    });

    describe('readRow not skip empty row', function () {
        test('offset 0, limit 0 - returns all rows including empty rows', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\count($rows))->toBe(14); // 10 data rows + 4 empty rows
            expect(\array_keys($rows))->toBe([2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]);
        });

        test('offset 0, limit 1 - returns first row (data row)', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 1,
            ]));

            expect(\count($rows))->toBe(1);
            expect(\array_keys($rows))->toBe([2]);
            $firstRow = \array_values($rows)[0];
            expect($firstRow)->toBe(['id' => '1', 'name' => 'Alice', 'department' => 'Engineering', 'salary' => '75000', 'status' => 'Active']);
        });

        test('offset 1, limit 0 - skips first row, returns rest', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 1,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\count($rows))->toBe(13);
            expect(\array_keys($rows))->toBe([3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]);
        });

        test('offset 1, limit 1 - skips first, returns second row (Bob)', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 1,
                CSV::OPTION_LIMIT => 1,
            ]));

            expect(\count($rows))->toBe(1);
            expect(\array_keys($rows))->toBe([3]);
            $firstRow = \array_values($rows)[0];
            expect($firstRow)->toBe(['id' => '2', 'name' => 'Bob', 'department' => 'Marketing', 'salary' => '65000', 'status' => 'Active']);
        });

        test('offset 20 (exceeds total) - returns empty', function (CSV $csv) {
            $rows = \iterator_to_array($csv->readRow([
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 20,
                CSV::OPTION_LIMIT => 1,
            ]));

            expect(\count($rows))->toBe(0);
        });
    });

    describe('readRows skip empty row', function () {
        test('offset 0, limit 0 - returns all data rows chunked', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(3, [
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1, 2, 3]);

            expect(\array_keys($chunks[0]))->toBe([2, 3, 5]);
            expect(\array_keys($chunks[1]))->toBe([7, 8, 10]);
            expect(\array_keys($chunks[2]))->toBe([11, 13, 14]);
            expect(\array_keys($chunks[3]))->toBe([15]);

            expect(\count($chunks[0]))->toBe(3);
            expect(\count($chunks[1]))->toBe(3);
            expect(\count($chunks[2]))->toBe(3);
            expect(\count($chunks[3]))->toBe(1);
        });

        test('offset 0, limit 3 - returns first 3 data rows chunked', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(2, [
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 3,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1]);

            expect(\count($chunks[0]))->toBe(2);
            expect(\count($chunks[1]))->toBe(1);

            expect(\array_keys($chunks[0]))->toBe([2, 3]);
            expect(\array_keys($chunks[1]))->toBe([5]);
        });

        test('offset 2, limit 0 - skips first 2 data rows, returns rest chunked', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(3, [
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 2,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1, 2]);

            expect(\count($chunks[0]))->toBe(3);
            expect(\count($chunks[1]))->toBe(3);
            expect(\count($chunks[2]))->toBe(2);

            expect(\array_keys($chunks[0]))->toBe([5, 7, 8]);
        });

        test('offset 2, limit 4 - skips first 2 data rows, returns next 4 chunked', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(2, [
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 2,
                CSV::OPTION_LIMIT => 4,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1]);

            expect(\count($chunks[0]))->toBe(2);
            expect(\count($chunks[1]))->toBe(2);

            expect(\array_keys($chunks[0]))->toBe([5, 7]);
            expect(\array_keys($chunks[1]))->toBe([8, 10]);
        });

        test('offset 10 (exceeds total), limit 0 - returns empty chunks', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(3, [
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 10,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\count($chunks))->toBe(0);
        });

        test('offset 0, limit 0 with chunk size 1 - each row is its own chunk', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(1, [
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 3,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1, 2]);

            expect(\count($chunks[0]))->toBe(1);
            expect(\count($chunks[1]))->toBe(1);
            expect(\count($chunks[2]))->toBe(1);

            expect(\array_keys($chunks[0]))->toBe([2]);
            expect(\array_keys($chunks[1]))->toBe([3]);
            expect(\array_keys($chunks[2]))->toBe([5]);
        });
    });

    describe('readRows not skip empty row', function () {
        test('offset 0, limit 0 - returns all rows including empty rows chunked', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(5, [
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1, 2]);

            expect(\count($chunks[0]))->toBe(5);
            expect(\count($chunks[1]))->toBe(5);
            expect(\count($chunks[2]))->toBe(4);

            expect(\array_keys($chunks[0]))->toBe([2, 3, 4, 5, 6]);
            expect(\array_keys($chunks[1]))->toBe([7, 8, 9, 10, 11]);
            expect(\array_keys($chunks[2]))->toBe([12, 13, 14, 15]);
        });

        test('offset 0, limit 5 - returns first 5 rows chunked', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(2, [
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 5,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1, 2]);

            expect(\count($chunks[0]))->toBe(2);
            expect(\count($chunks[1]))->toBe(2);
            expect(\count($chunks[2]))->toBe(1);

            expect(\array_keys($chunks[0]))->toBe([2, 3]);
            expect(\array_keys($chunks[1]))->toBe([4, 5]);
            expect(\array_keys($chunks[2]))->toBe([6]);
        });

        test('offset 3, limit 0 - skips first 3 rows, returns rest chunked', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(4, [
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 3,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1, 2]);

            expect(\count($chunks[0]))->toBe(4);
            expect(\count($chunks[1]))->toBe(4);
            expect(\count($chunks[2]))->toBe(3);

            expect(\array_keys($chunks[0]))->toBe([5, 6, 7, 8]);
        });

        test('offset 3, limit 6 - skips first 3 rows, returns next 6 chunked', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(3, [
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 3,
                CSV::OPTION_LIMIT => 6,
            ]));

            expect(\array_keys($chunks))->toBe([0, 1]);

            expect(\count($chunks[0]))->toBe(3);
            expect(\count($chunks[1]))->toBe(3);

            expect(\array_keys($chunks[0]))->toBe([5, 6, 7]);
            expect(\array_keys($chunks[1]))->toBe([8, 9, 10]);
        });

        test('offset 20 (exceeds total), limit 0 - returns empty chunks', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(3, [
                CSV::OPTION_SKIP_EMPTY_ROW => false,
                CSV::OPTION_OFFSET => 20,
                CSV::OPTION_LIMIT => 0,
            ]));

            expect(\count($chunks))->toBe(0);
        });

        test('chunk size larger than available rows - yields single chunk', function (CSV $csv) {
            $chunks = \iterator_to_array($csv->readRows(20, [
                CSV::OPTION_SKIP_EMPTY_ROW => true,
                CSV::OPTION_OFFSET => 0,
                CSV::OPTION_LIMIT => 5,
            ]));

            expect(\array_keys($chunks))->toBe([0]);

            expect(\count($chunks[0]))->toBe(5);

            expect(\array_keys($chunks[0]))->toBe([2, 3, 5, 7, 8]);
        });
    });

    test('readRows throws exception for non-positive size', function (CSV $csv) {
        $csv->readRows(0, [
            CSV::OPTION_OFFSET => 0,
            CSV::OPTION_LIMIT => 0,
        ])->current();
    })->throws(\InvalidArgumentException::class);
});
