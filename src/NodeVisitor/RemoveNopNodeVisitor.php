<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use PhpParser\NodeVisitorAbstract;

final class RemoveNopNodeVisitor extends NodeVisitorAbstract
{
    //    public function format(Node ...$nodes): void
    //    {
    //        foreach ($nodes as $node) {
    //            $this->removeNode($node);
    //        }
    //    }
    //
    //    public function matches(Node $node): bool
    //    {
    //        return $node instanceof Nop;
    //    }
}
