<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Interface;

use PhpParser\Node;

interface NodeVisitorInterface
{
    public function afterTraverse(array $nodes): void;

    public function beforeTraverse(array $nodes): void;

    public function enterNode(Node $node): void;

    public function leaveNode(Node $node): void;
}
