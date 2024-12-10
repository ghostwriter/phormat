<?php

declare(strict_types=1);

use Ghostwriter\Phormat\NodeVisitor\PERCSNodeVisitor;
use Ghostwriter\Phormat\PhormatConfig;

/** @var non-empty-string $workingDirectory */
$workingDirectory = \getcwd() ?: __DIR__;

/** @psalm-suppress UncaughtThrowInGlobalScope */
return PhormatConfig::new()
    ->visitors(PERCSNodeVisitor::class)
    ->paths(
        $workingDirectory . '/bin',
        $workingDirectory . '/src',
        $workingDirectory . '/tests'
    )->skip($workingDirectory . '/vendor')->skipVisitors([
        PERCSNodeVisitor::class => [$workingDirectory . '/tests/Fixture'],
    ]);
