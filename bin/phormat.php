<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Console;

use ErrorException;
use Ghostwriter\Phormat\Phormat;

use const DIRECTORY_SEPARATOR;
use const STDERR;

(static function (string $autoloader): void {
    \set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
        throw new ErrorException($message, 255, $severity, $file, $line);
    });

    if (! \file_exists($autoloader)) {
        \fwrite(STDERR, '[ERROR]Cannot locate "' . $autoloader . '"\n please run "composer install"\n');
        exit;
    }

    require $autoloader;

    /**
     * #BlackLivesMatter.
     */
    Phormat::new()->run();

    \restore_error_handler();
})($_composer_autoload_path ?? \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php');
