<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class LocalGetterToPropertyVisitor extends NodeVisitorAbstract
{
    /** @var array<string,string> */
    private array $gettersToProperties = [];

    #[Override]
    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            $this->identifyGetterMethods($node);
        }

        if ($node instanceof PropertyFetch) {
            return $this->replaceGetterWithProperty($node);
        }

        return null;
    }

    #[Override]
    public function leaveNode(Node $node): ?int
    {
        if ($node instanceof ClassMethod && isset($this->gettersToProperties[$node->name->toString()])) {
            return NodeTraverser::REMOVE_NODE;
        }

        return null;
    }

    private function identifyGetterMethods(ClassMethod $classMethod): void
    {
        if (! $classMethod->isPublic() || \count($classMethod->params) > 0 || $classMethod->getStmts() === null) {
            return;
        }

        $stmts = $classMethod->getStmts();
        if (\count($stmts) === 1 && $stmts[0] instanceof Return_) {
            $returnExpr = $stmts[0]->expr;
            if ($returnExpr instanceof PropertyFetch && $returnExpr->var instanceof Variable && $returnExpr->var->name === 'this') {
                $this->gettersToProperties[$classMethod->name->toString()] = $returnExpr->name->toString();
            }
        }
    }

    private function replaceGetterWithProperty(PropertyFetch $propertyFetch): ?Node
    {
        if ($propertyFetch->var instanceof Variable && $propertyFetch->var->name === 'this') {
            $methodName = $propertyFetch->name->toString();
            if (isset($this->gettersToProperties[$methodName])) {
                return new PropertyFetch(new Variable('this'), $this->gettersToProperties[$methodName]);
            }
        }

        return null;
    }
}
