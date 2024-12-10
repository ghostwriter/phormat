<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Match_;

final class SortMatchExpressionsAlphabeticallyNodeVisitor extends AbstractNodeVisitor
{
    #[Override]
    public function format(Node ...$node): void
    {

    }

    //    public function format(Node $node): null|array|int|Node
    //    {
    //        return $this->sortMatch($node);
    //    }
    //
    //    public function matches(Node $node): bool
    //    {
    //        return $node instanceof Match_;
    //    }

    #[Override]
    public function leaveNode(Node $node): ?Node
    {
        if (! $node instanceof Match_) {
            return null;
        }

        //        usort($node->arms, static function (MatchArm $a, MatchArm $b) {
        //            $aCondition = $a->conds;
        //            $bCondition = $b->conds;
        //
        //            /**
        //             * @psalm-assert-if-true null $aCondition
        //             */
        //            $aIsDefault = $aCondition === null;
        //
        //            /**
        //             * @psalm-assert-if-true null $bCondition
        //             */
        //            $bIsDefault = $bCondition === null;
        //
        //            if ($aIsDefault && $bIsDefault) {
        //                return 0;
        //            }
        //
        //            if ($aIsDefault) {
        //                return 1;
        //            }
        //
        //            if ($bIsDefault) {
        //                return -1;
        //            }
        //
        //            return $aCondition[0]->name?->toString() <=> $bCondition[0]->name?->toString();
        //        });
        return $node;
    }
}
