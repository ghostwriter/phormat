<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Interface;

use PhpParser\Node;

interface FormatterInterface
{
    public function format(Node $node): null|array|int|Node;
}
