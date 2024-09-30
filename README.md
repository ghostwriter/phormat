# Phormat

[![Automation](https://github.com/ghostwriter/phormat/actions/workflows/automation.yml/badge.svg)](https://github.com/ghostwriter/phormat/actions/workflows/automation.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/phormat?color=8892bf)](https://www.php.net/supported-versions)
[![GitHub Sponsors](https://img.shields.io/github/sponsors/ghostwriter?label=Sponsor+@ghostwriter/phormat&logo=GitHub+Sponsors)](https://github.com/sponsors/ghostwriter)
[![Code Coverage](https://codecov.io/gh/ghostwriter/phormat/branch/main/graph/badge.svg)](https://codecov.io/gh/ghostwriter/phormat)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/phormat/coverage.svg)](https://shepherd.dev/github/ghostwriter/phormat)
[![Psalm Level](https://shepherd.dev/github/ghostwriter/phormat/level.svg)](https://psalm.dev/docs/running_psalm/error_levels)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/phormat)](https://packagist.org/packages/ghostwriter/phormat)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/phormat?color=blue)](https://packagist.org/packages/ghostwriter/phormat)

PHP code formatter.

> [!WARNING]
>
> This project is not finished yet, work in progress.

## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/phormat
```

### Star ⭐️ this repo if you find it useful

You can also star (🌟) this repo to find it easier later.

## Usage

```php
vendor/bin/phormat <path> --dry-run
```

## Configuration

```php
<?php

declare(strict_types=1);

use Ghostwriter\Phormat\NodeVisitor\PERCSNodeVisitor;
use Ghostwriter\Phormat\PhormatConfig;

/** @var non-empty-string $workingDirectory */
$workingDirectory = \getcwd() ?: __DIR__;

/** @psalm-suppress UncaughtThrowInGlobalScope */
return PhormatConfig::new()
    ->paths($workingDirectory . '/bin', $workingDirectory . '/src', $workingDirectory . '/tests')
    ->skip($workingDirectory . '/.cache', $workingDirectory . '/docs', $workingDirectory . '/vendor')
    ->skipVisitors([
        PERCSNodeVisitor::class => [$workingDirectory . '/tests/Fixture'],
    ]);
```

### Credits

- [Nathanael Esayeas](https://github.com/ghostwriter)
- [Nikita Popov `nikic/php-parser`](https://github.com/nikic/php-parser)
- [All Contributors](https://github.com/ghostwriter/phormat/contributors)

### Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information on what has changed recently.

### License

Please see [LICENSE](./LICENSE) for more information on the license that applies to this project.

### Security

Please see [SECURITY.md](./SECURITY.md) for more information on security disclosure process.
