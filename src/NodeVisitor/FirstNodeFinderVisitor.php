<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Closure;
use Override;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class FirstNodeFinderVisitor extends NodeVisitorAbstract
{
    private null|Node $foundNode = null;

    public function __construct(
        private readonly Closure $predicate
    ) {
    }

    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        $this->foundNode = null;
        return null;
    }

    #[Override]
    public function enterNode(Node $node): null|int|Node
    {
        if (! ($this->predicate)($node)) {
            return null;
        }

        $this->foundNode = $node;
        return NodeTraverser::STOP_TRAVERSAL;
    }

    public function getFoundNode(): ?Node
    {
        return $this->foundNode;
    }
}
