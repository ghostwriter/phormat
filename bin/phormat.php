#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Console;

use Ghostwriter\Phormat\Phormat;

use const DIRECTORY_SEPARATOR;
use const STDERR;

use function dirname;
use function fwrite;
use function sprintf;

/** @var ?string $_composer_autoload_path */
(static function (string $composerAutoloadPath): void {
    /** @psalm-suppress UnresolvableInclude */
    require $composerAutoloadPath ?: fwrite(
        STDERR,
        sprintf('[ERROR]Cannot locate "%s"\n please run "composer install"\n', $composerAutoloadPath)
    ) && exit(1);

    /**
     * #BlackLivesMatter.
     */
    Phormat::new()->run();
})(
    $_composer_autoload_path ?? dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php'
);
