<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\NodeVisitorAbstract;

final class DeclareStrictTypesNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private bool $hasDeclareStrictTypes = false,
        private readonly Declare_ $declare = new Declare_([
            new DeclareDeclare(new Identifier('strict_types'), new LNumber(1)),
        ])
    ) {
    }

    #[Override]
    public function afterTraverse(array $nodes): ?array
    {
        if ($this->hasDeclareStrictTypes) {
            return null;
        }

        \array_unshift($nodes, $this->declare);
        return $nodes;
    }

    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        $this->hasDeclareStrictTypes = false;
        $i = 3;
        foreach ($nodes as $node) {
            if ($node instanceof Declare_) {
                $this->hasDeclareStrictTypes = true;
                return null;
            }

            if (--$i < 0) {
                break;
            }
        }

        return null;
    }
}
