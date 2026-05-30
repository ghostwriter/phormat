# Phormat

[![Automation](https://github.com/ghostwriter/phormat/actions/workflows/automation.yml/badge.svg)](https://github.com/ghostwriter/phormat/actions/workflows/automation.yml)
[![PHP Version](https://badgen.net/packagist/php/ghostwriter/phormat?color=777BB4)](https://www.php.net/supported-versions)
[![Packagist Downloads](https://badgen.net/packagist/dt/ghostwriter/phormat?color=F28D1A)](https://packagist.org/packages/ghostwriter/phormat)
[![PayPal](https://img.shields.io/badge/paypal-@codepoet-0079C1?logo=paypal&logoColor=002991)](https://paypal.me/codepoet)
[![Sponsors via GitHub](https://img.shields.io/github/sponsors/ghostwriter?label=Sponsor+@ghostwriter/phormat&logo=GitHub+Sponsors)](https://github.com/sponsors/ghostwriter)

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

wip - work in progress

```php
<?php

declare(strict_types=1);

use Ghostwriter\Phormat\NodeVisitor\ChangeToShortArrayNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\DeclareStrictTypesNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\ImportFullyQualifiedNamesNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\MakeClosureAndFunctionStaticNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\PERCSNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\SortClassLikeMemberStatementsAlphabeticallyNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\SortClassLikeStatementsAlphabeticallyNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\SortMatchExpressionsAlphabeticallyNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\SortUseStatementsAlphabeticallyNodeVisitor;
use Ghostwriter\Phormat\PhormatConfig;

/** @var non-empty-string $workingDirectory */
$workingDirectory = \getcwd() ?: __DIR__;

/** @psalm-suppress UncaughtThrowInGlobalScope */
return PhormatConfig::new()
    ->visitors([
        PERCSNodeVisitor::class,
        // ChangeToShortArrayNodeVisitor::class,
        // DeclareStrictTypesNodeVisitor::class,
        // ImportFullyQualifiedNamesNodeVisitor::class,
        // MakeClosureAndFunctionStaticNodeVisitor::class,
        // SortClassLikeMemberStatementsAlphabeticallyNodeVisitor::class,
        // SortClassLikeStatementsAlphabeticallyNodeVisitor::class,
        // SortMatchExpressionsAlphabeticallyNodeVisitor::class,
        // SortUseStatementsAlphabeticallyNodeVisitor::class,
    ]);
    ->paths([
        $workingDirectory . '/bin',
        $workingDirectory . '/src', 
        $workingDirectory . '/tests'
    ])
    ->exclude([
        PERCSNodeVisitor::class => [
            $workingDirectory . '/resources',
        ],
        $workingDirectory . '/.cache',
        $workingDirectory . '/docs',
        $workingDirectory . '/tests/fixture'
        $workingDirectory . '/vendor',
    ]);
    ->phpVersion(8, 2); // To use a specific PHP version when parsing the code
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
