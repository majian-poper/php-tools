<?php

namespace PHPTools\LaravelDatabaseTask\Concerns;

/**
 * @deprecated since 0.2.0 — use the dedicated Asxxx traits under {@see Input} instead.
 *
 * ```php
 * use PHPTools\LaravelDatabaseTask\Concerns\Input\AsQuery;
 *
 * class MyInput
 * {
 *     use AsQuery;
 * }
 * ```
 */
trait InteractsWithInput
{
    use Input\AsBoolean;
    use Input\AsDateTime;
    use Input\AsFile;
    use Input\AsNumber;
    use Input\AsQuery;
    use Input\AsSelect;
}
