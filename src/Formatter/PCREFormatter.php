<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Formatter;

use Ghostwriter\Phormat\NodeVisitor\AbstractNodeVisitor;
use Override;
use PhpParser\Node;

final class PCREFormatter extends AbstractNodeVisitor
{
    #[Override]
    public function format(Node $node): null|array|int|Node
    {
        return $node;
    }
}
