<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor\Core;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class FileNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private readonly string $path
    ) {
        if (! \file_exists($path)) {
            throw new InvalidArgumentException(\sprintf('File "%s" does not exist', $path));
        }
    }

    public function enterNode(Node $node): Node
    {
        $node->setAttribute(self::class, $this->path);
        return $node;
    }
}
