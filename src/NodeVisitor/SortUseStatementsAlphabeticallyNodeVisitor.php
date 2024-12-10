<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Ghostwriter\Phormat\Exception\ShouldNotHappenException;
use Ghostwriter\Phormat\FormatterInterface;
use Override;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\PrettyPrinter\Standard;

final class SortUseStatementsAlphabeticallyNodeVisitor extends AbstractNodeVisitor implements FormatterInterface
{
    private const int SAME = 0;

    private const int LEFT_BEFORE_RIGHT = -1;

    private const int RIGHT_BEFORE_LEFT = 1;

    //    public function __construct(
    //        private readonly Standard $standard
    //    ) {
    //    }

    //    #[Override]
    //    public function compareUse(Use_ $left, Use_ $right): int
    //    {
    //        return \strcasecmp($this->standard->prettyPrint($left->uses), $this->standard->prettyPrint($right->uses));
    //    }

    #[Override]
    public function format(Node $node): null|array|int|Node
    {
        return $this->sort($node);
    }

    #[Override]
    public function leaveNode(Node $node): ?Node
    {
        if (! $node instanceof Namespace_) {
            return null;
        }

        \usort($node->stmts, function (Node $left, Node $right): int {
            if (! $left instanceof Use_ || ! $right instanceof Use_) {
                return self::SAME;
            }

            return match ($left->type) {
                default => throw new ShouldNotHappenException(),
                Use_::TYPE_CONSTANT => match ($right->type) {
                    default => throw new ShouldNotHappenException(),
                    Use_::TYPE_CONSTANT => $this->compareUse($left, $right),
                    Use_::TYPE_FUNCTION => self::LEFT_BEFORE_RIGHT,
                    Use_::TYPE_NORMAL => self::RIGHT_BEFORE_LEFT,
                },
                Use_::TYPE_FUNCTION => match ($right->type) {
                    default => throw new ShouldNotHappenException(),
                    Use_::TYPE_CONSTANT, Use_::TYPE_NORMAL => self::RIGHT_BEFORE_LEFT,
                    Use_::TYPE_FUNCTION => $this->compareUse($left, $right),
                },
                Use_::TYPE_NORMAL => match ($right->type) {
                    default => throw new ShouldNotHappenException(),
                    Use_::TYPE_CONSTANT, Use_::TYPE_FUNCTION => self::LEFT_BEFORE_RIGHT,
                    Use_::TYPE_NORMAL => $this->compareUse($left, $right),
                },
            };
        });
        return $node;
    }

    #[Override]
    public function matches(Node $node): bool
    {
        return $node instanceof Namespace_;
    }
}
