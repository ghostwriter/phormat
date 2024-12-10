<?php

namespace Ghostwriter\Phormat\Interface;

use PhpParser\Node;

interface MiddlewareInterface
{
    public function process(Node $node, FormatterInterface $formatter): null|array|int|Node;
}
