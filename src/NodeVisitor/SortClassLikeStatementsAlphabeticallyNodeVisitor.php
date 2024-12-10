<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;

final class SortClassLikeStatementsAlphabeticallyNodeVisitor extends NodeVisitorAbstract
{
    #[Override]
    public function enterNode(Node $node): ?Namespace_
    {
        if (! $node instanceof Namespace_) {
            return null;
        }

        \usort($node->stmts, static function (Node $a, Node $b): int {
            if (! $a instanceof ClassLike) {
                return 0;
            }

            if (! $b instanceof ClassLike) {
                return 0;
            }

            return $a->name?->toString() <=> $b->name?->toString();
        });
        return $node;

        // return $this->sort($node);
    }
}
