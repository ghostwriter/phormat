<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeVisitorAbstract;

final class SortClassLikeMemberStatementsAlphabeticallyNodeVisitor extends NodeVisitorAbstract
{
    public function format(Node $node): null|array|int|Node
    {
        if (! $node instanceof ClassLike) {
            return null;
        }

        return $this->sort($node);
    }
}
