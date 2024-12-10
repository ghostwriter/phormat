<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\NodeTraverser;

final class MakeClosureAndFunctionStaticNodeVisitor extends AbstractNodeVisitor
{
    public function findThisVariable(array|Node $nodes): bool
    {
        if ($nodes instanceof Node) {
            $nodes = [$nodes];
        }

        $predicate = static fn (Node $node): bool => $node instanceof Variable && $node->name === 'this';
        $firstNodeFinderVisitor = new FirstNodeFinderVisitor($predicate);
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($firstNodeFinderVisitor);
        $nodeTraverser->traverse($nodes);
        return $firstNodeFinderVisitor->getFoundNode() instanceof Node;
    }

    #[Override]
    public function format(Node ...$nodes): void
    {
        foreach ($nodes as $node) {
            match (true) {
                $node instanceof ArrowFunction, $node instanceof Closure => match (true) {
                    $node->static, $this->findThisVariable($node) => null,
                    default => $this->makeStatic($node),
                },
                default => null,
            };
        }
    }

    public function format2(Node $node): null|array|int|Node
    {
        return match (true) {
            $node instanceof ArrowFunction, $node instanceof Closure => match (true) {
                $node->static, $this->findThisVariable($node) => null,
                default => $this->makeStatic($node),
            },
            default => null,
        };
    }

    private function makeStatic(ArrowFunction|Closure $node): ?Node
    {
        $node->static = true;
        return $node;
    }
}
