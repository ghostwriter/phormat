#!/usr/bin/env php
<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Console;

use ErrorException;
use Ghostwriter\Phormat\Phormat;

use const DIRECTORY_SEPARATOR;
use const STDERR;

use function dirname;
use function error_reporting;
use function file_exists;
use function fwrite;
use function set_error_handler;

(
    static function (string $autoloader): void {
        if (! file_exists($autoloader)) {
            $message = '[ERROR]Cannot locate "' . $autoloader . '"\\n please run "composer install"\\n';

            fwrite(STDERR, $message);

            exit();
        }

        require $autoloader;

        set_error_handler(
            static function (int $severity, string $message, string $file, int $line): void {
                if (! (error_reporting() & $severity)) {
                    return;
                }

                throw new ErrorException($message, 0, $severity, $file, $line);
            }
        );

        /**
         * #BlackLivesMatter.
         */
        Phormat::new()->run();
    }
)($_composer_autoload_path ?? dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php');
