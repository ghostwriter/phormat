<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\Node;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitorAbstract;

final class AddMissingThrowsDocblock extends NodeVisitorAbstract
{
    /** @var array<string,array<string>> */
    private array $throwsByFunction = [];

    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        $this->throwsByFunction = [];
        return null;
    }

    #[Override]
    public function enterNode(Node $node): ?Node
    {
        if (! $node instanceof Throw_) {
            return null;
        }

        $functionNode = $node->getAttribute('parent');
        if (! $functionNode instanceof FunctionLike) {
            return null;
        }

        $functionName = $this->getFunctionName($functionNode);

        $this->throwsByFunction[$functionName][] = $node->expr->class->toString();

        return null;
    }

    #[Override]
    public function leaveNode(Node $node): ?Node
    {
        if (! $node instanceof FunctionLike) {
            return null;
        }

        //        if ($node instanceof ClassMethod || $node instanceof Function_) {
        //        $functionName = $this->getFunctionName($node);
        //
        //        if (array_key_exists($functionName, $this->throwsByFunction)) {
        //            dump($functionName);
        //            $docComment = $node->getDocComment();
        //            $throwsTypes = array_unique($this->throwsByFunction[$functionName]);
        //            $throwsDoc = array_map(static fn ($type) => "@throws \\{$type}", $throwsTypes);
        //            $newDocCommentText = $docComment ? $this->updateDocComment(
        //                $docComment->getText(),
        //                $throwsDoc
        //            ) : $this->createDocComment($throwsDoc);
        //            $node->setDocComment(new Doc($newDocCommentText));
        //        }
        //        }
        return null;
    }

    private function createDocComment(array $throwsDoc): string
    {
        return "/**\n" . \implode("\n", $throwsDoc) . "\n*/";
    }

    private function getFunctionName(Node $node): string
    {
        if ($node instanceof ClassMethod) {
            $className = $node->getAttribute('parent')
                ->namespacedName->toString();

            return \sprintf('%s::%s', $className, $node->name->toString());
        }

        if ($node instanceof Function_) {
            return $node->name->toString();
        }

        return '';
    }

    private function updateDocComment(string $existingDocComment, array $throwsDoc): string
    {
        return \rtrim($existingDocComment, '*/') . "\n" . \implode("\n", $throwsDoc) . "\n*/";
    }
}
