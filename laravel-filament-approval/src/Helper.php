<?php

namespace PHPTools\LaravelFilamentApproval;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

class Helper
{
    public static function getFilamentVersion(): int
    {
        static $majorVersion;

        if (isset($majorVersion)) {
            return $majorVersion;
        }

        $versionParser = new VersionParser;

        foreach ([3, 4, 5] as $major) {
            if (InstalledVersions::satisfies($versionParser, 'filament/filament', "^{$major}.0")) {
                return $majorVersion = $major;
            }
        }

        throw new \RuntimeException('Unsupported Filament version.');
    }
}
