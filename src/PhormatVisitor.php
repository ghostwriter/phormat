<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use Ghostwriter\Phormat\NodeVisitor\AbstractNodeVisitor;
use Override;
use PhpParser\Node;

final class PhormatVisitor extends AbstractNodeVisitor
{
    /**
     * @var FormatterInterface[]
     */
    public array $formatters = [];

    private bool $formatted = false;

    #[Override]
    public function enterNode(Node $node): ?Node
    {
        foreach ($this->formatters as $formatter) {
            if (! $formatter->matches($node)) {
                continue;
            }

            if (! $formatter->supports($node)) {
                continue;
            }

            $this->formatted = true;
            $formatter->format($node);
        }

        return $this->formatted ? $node : null;
    }

    #[Override]
    public function format(Node ...$node): void
    {
    }

    #[Override]
    public function matches(Node $node): bool
    {
        return true;
    }
}
