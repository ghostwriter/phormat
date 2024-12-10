<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use PhpParser\Node;

interface NodeVisitorInterface
{
    public function after(array $nodes): ?array;

    public function before(array $nodes): ?array;

    public function enter(Node $node): null|int|Node;

    public function leave(Node $node): null|array|int|Node;
}
