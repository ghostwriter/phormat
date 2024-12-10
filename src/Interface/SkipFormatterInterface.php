<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Interface;

use PhpParser\Node;

interface SkipFormatterInterface extends FormatterInterface
{
    public function skip(Node $node): bool;
}
