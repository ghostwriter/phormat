<?php

declare(strict_types=1);

use Ghostwriter\Phormat\NodeVisitor\AddMissingThrowsDocblock;
use Ghostwriter\Phormat\NodeVisitor\ChangeToShortArrayNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\DeclareStrictTypesNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\ImportFullyQualifiedNamesNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\PERCodingStyle\PsrOneNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\SortUseStatementsAlphabeticallyNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\UseArrowFunctionsNodeVisitor;
use Ghostwriter\Phormat\PhormatConfig;

/** @var non-empty-string $workingDirectory */
$workingDirectory = \getcwd() ?: __DIR__;

/**
 * @psalm-suppress UncaughtThrowInGlobalScope
 */
return PhormatConfig::new(...[
    ImportFullyQualifiedNamesNodeVisitor::class,
    PsrOneNodeVisitor::class,
    AddMissingThrowsDocblock::class,
    //        PERCSNodeVisitor::class,
    UseArrowFunctionsNodeVisitor::class,
    ChangeToShortArrayNodeVisitor::class,
    DeclareStrictTypesNodeVisitor::class,
    //    AddNewlineBetweenNodes::class,
    //    MakeClosureAndFunctionStaticNodeVisitor::class,
    //    RemoveNopNodeVisitor::class,
    //    SortClassLikeMemberStatementsAlphabeticallyNodeVisitor::class,
    //    SortClassLikeStatementsAlphabeticallyNodeVisitor::class,
    //    SortMatchExpressionsAlphabeticallyNodeVisitor::class,
    //    SortMatchExpressionsAlphabeticallyNodeVisitor::class,
    //    SortNodeVisitor::class,
    SortUseStatementsAlphabeticallyNodeVisitor::class,
])
    ->paths(...\array_filter(
        [
            __FILE__,
            $workingDirectory,
            $workingDirectory . '/bin',
            $workingDirectory . '/config',
            $workingDirectory . '/data',
            $workingDirectory . '/docs',
            $workingDirectory . '/ecs.php',
            $workingDirectory . '/index.php',
            $workingDirectory . '/module',
            $workingDirectory . '/phormat.php',
            $workingDirectory . '/public',
            $workingDirectory . '/rector.php',
            $workingDirectory . '/resource',
            $workingDirectory . '/src',
            $workingDirectory . '/test',
            $workingDirectory . '/tests',
        ],
        static fn (string $path): bool => \file_exists($path)
    ))
    ->skip(...\array_filter(
        [$workingDirectory . '/tests/Fixture', $workingDirectory . '/.cache', $workingDirectory . '/vendor'],
        static fn (string $path): bool => \file_exists($path)
    ));
