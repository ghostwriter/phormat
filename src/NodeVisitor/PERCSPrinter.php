<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use PhpParser\Node\Expr;
use PhpParser\PrettyPrinter\Standard;

final readonly class PERCSPrinter
{
    public function __construct(
        private Standard $standard
    ) {
    }

    public function print(array $nodes): string
    {
        return $this->standard->prettyPrint($nodes);
    }

    public function printExpr(Expr $expr): string
    {
        return $this->standard->prettyPrintExpr($expr);
    }

    public function printFile(array $nodes): string
    {
        return $this->standard->prettyPrintFile($nodes);
    }

    public function printFormat(array $nodes): string
    {
        return $this->standard->printFormatPreserving(
            $nodes,
            $nodes[0]->getAttribute('phormat.originalStmts', []) ?? [],
            $nodes[0]->getAttribute('phormat.originalTokens', []) ?? []
        );
    }
}
