<?php

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PHPTools\LaravelDatabaseTask\Inputs\FileInput;
use PHPUnit\Framework\TestCase;

uses(TestCase::class);

function fileInputStream(string $contents)
{
    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $contents);
    rewind($stream);

    return $stream;
}

function temporaryUploadedFileWithContents(string $contents): TemporaryUploadedFile
{
    return new class($contents) extends TemporaryUploadedFile
    {
        public function __construct(private readonly string $contents) {}

        public function readStream()
        {
            return fileInputStream($this->contents);
        }
    };
}

test('a file input materializes and caches a stream', function (): void {
    $input = new FileInput;
    $input->value(fileInputStream('input contents'));

    $file = $input->getValue();

    expect($file)->toBeInstanceOf(SplFileObject::class)
        ->and($file->fread(100))->toBe('input contents')
        ->and($input->getValue())->toBe($file);
});

test('a file input materializes a temporary uploaded file', function (): void {
    $input = new FileInput;
    $uploadedFile = temporaryUploadedFileWithContents('uploaded contents');

    $input->value($uploadedFile);
    $file = $input->getValue();

    expect($input->getUploadedFile())->toBe($uploadedFile)
        ->and($file->fread(100))->toBe('uploaded contents');
});

test('an empty file input returns null', function (): void {
    expect((new FileInput)->getValue())->toBeNull();
});
