<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class HashNodeVisitor extends NodeVisitorAbstract
{
    #[Override]
    public function enterNode(Node $node): ?Node
    {
        $node->setAttribute(self::class, \spl_object_hash($node));
        return $node;
    }
}
