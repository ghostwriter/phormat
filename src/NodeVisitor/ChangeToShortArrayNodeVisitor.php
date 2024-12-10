<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\NodeVisitorAbstract;

final class ChangeToShortArrayNodeVisitor extends NodeVisitorAbstract
{
    #[Override]
    public function leaveNode(Node $node): ?Node
    {
        if (! $node instanceof Array_) {
            return null;
        }

        $node->setAttribute('kind', Array_::KIND_SHORT);

        return $node;
    }
}
