<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitorAbstract;

final class AddNewlineBetweenNodes extends NodeVisitorAbstract
{
    public function __construct(
        private readonly Nop $nop = new Nop()
    ) {
    }

    #[Override]
    public function leaveNode(Node $node): null|int|Node
    {
        if ($node instanceof Nop) {
            return self::REMOVE_NODE;
        }

        $subNodeNames = $node->getSubNodeNames();

        if (! \in_array('stmts', $subNodeNames, true)) {
            return null;
        }

        \dump($node::class);

        $node->stmts = $this->ensureNewlineBetweenNodes($node->stmts ?? []);
        //        if (! property_exists($node, 'stmts')) {
        //            return null;
        //        }
        //
        foreach ($subNodeNames as $subNodeName) {
            $subNode = $node->{$subNodeName};
            if (! $subNode instanceof Node) {
                continue;
            }

            $this->leaveNode($subNode);
        }

        /** @var null|array $stmts */
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }

        $node->stmts = $this->ensureNewlineBetweenNodes($node->stmts);

        return $node;
    }

    /**
     * @param array<Stmt> $stmts
     *
     * @return array<Stmt>
     */
    private function ensureNewlineBetweenNodes(array $stmts): array
    {
        $lastNode = \array_key_last($stmts);

        /** @var array<Stmt> $newStmts */
        $newStmts = [];

        foreach ($stmts as $index => $node) {
            if ($node instanceof Nop) {
                continue;
            }

            $newStmts[] = $node;

            if ($lastNode === $index) {
                continue;
            }

            $newStmts[] = $this->nop;
        }

        return $newStmts;
    }
}
