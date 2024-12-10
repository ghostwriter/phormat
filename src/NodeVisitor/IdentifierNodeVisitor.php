<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class IdentifierNodeVisitor extends NodeVisitorAbstract
{
    #[Override]
    public function enterNode(Node $node): ?Node
    {
        $node->setAttribute(self::class, \md5(\spl_object_hash($node)));

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->{$subNodeName};
            if (\is_iterable($subNode)) {
                foreach ($subNode as $subNodeItem) {
                    if (! $subNodeItem instanceof Node) {
                        continue;
                    }

                    $this->enterNode($subNodeItem);
                }
            }

            if (! $subNode instanceof Node) {
                continue;
            }

            $this->enterNode($subNode);
        }

        return $node;
    }
}
