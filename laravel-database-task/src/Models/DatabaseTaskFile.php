<?php

namespace PHPTools\LaravelDatabaseTask\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DatabaseTaskFile extends Media
{
    public function toTempFileObject(): \SplTempFileObject
    {
        $resource = $this->stream();

        $file = new \SplTempFileObject;

        while (! \feof($resource)) {
            $file->fwrite(\fread($resource, 8192));
        }

        \fclose($resource);

        $file->rewind();

        return $file;
    }
}
