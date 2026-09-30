<?php

use PHPTools\LaravelDatabaseTask\Outputs\FileOutput;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

test('a file output proxies file methods and exposes its stream', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'dbtask_test_');
    $output = new FileOutput($path, 'w+');
    $output->fputcsv(['id', 'name'], ',', '"', '\\', "\n");
    $output->fputcsv([1, 'Ada'], ',', '"', '\\', "\n");

    $stream = $output->getStream();
    $contents = stream_get_contents($stream);
    fclose($stream);

    expect($output->getValue())->toBeInstanceOf(SplFileObject::class)
        ->and($contents)->toBe("id,name\n1,Ada\n");
});

test('a file output wraps an existing file', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'dbtask_test_');
    file_put_contents($path, 'existing contents');

    $output = new FileOutput($path, 'r');
    $file = $output->getValue();

    expect($file->fread(100))->toBe('existing contents');
});
