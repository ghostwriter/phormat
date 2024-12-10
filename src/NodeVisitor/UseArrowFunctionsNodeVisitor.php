<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeVisitorAbstract;

final class UseArrowFunctionsNodeVisitor extends NodeVisitorAbstract
    //final class UseArrowFunctionsNodeVisitor extends AbstractNodeVisitor implements FormatterInterface
{
    #[Override]
    public function enterNode(Node $node): ?Node
    {
        return match (true) {
            $node instanceof Closure => match (true) {
                $this->canBeConvertedToArrowFunction($node) => $this->convertClosureToArrowFunction($node),
                default => null,
            },
            default => null,
        };
    }

    private function canBeConvertedToArrowFunction(Closure $closure): bool
    {
        // Check if the closure has a single statement which is a return statement
        if (! \array_key_exists(0, $closure->stmts) || ! $closure->stmts[0] instanceof Return_) {
            return false;
        }

        // Check if the closure uses variables from the parent scope (it should not)
        foreach ($closure->uses as $use) {
            if (! $use instanceof ClosureUse) {
                return false;
            }
        }

        return true;
    }

    private function convertClosureToArrowFunction(Closure $closure): ArrowFunction
    {
        return new ArrowFunction([
            'static' => $closure->static,
            'byRef' => $closure->byRef,
            'params' => $closure->params,
            'returnType' => $closure->returnType,
            'expr' => $closure->stmts[0]->expr,
        ]);
    }
}
