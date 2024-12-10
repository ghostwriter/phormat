<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;

//final class PERCSNodeVisitor extends NodeVisitorAbstract
final class PERCSNodeVisitor extends AbstractNodeVisitor
{
    #[Override]
    public function format(Node $node): null|array|int|Node
    {
        return $node;
    }
}
