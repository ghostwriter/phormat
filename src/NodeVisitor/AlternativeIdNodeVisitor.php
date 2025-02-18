<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;

use function spl_object_hash;

final class AlternativeIdNodeVisitor extends AbstractNodeVisitor
{
    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        foreach ($nodes as $node) {
            $node->setAttribute(self::class, spl_object_hash($node));
        }

        return $nodes;
    }
}
