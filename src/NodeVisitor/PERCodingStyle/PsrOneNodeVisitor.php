<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor\PERCodingStyle;

use Ghostwriter\CaseConverter\CaseConverter;
use Override;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;

final class PsrOneNodeVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private readonly CaseConverter $caseConverter
    ) {
    }

    #[Override]
    public function leaveNode(Node $node): ?Node
    {
        return match (true) {
            // Ensure class name PascalCase
            $node instanceof Class_ => $this->validateClassName($node),
            // Ensure method name camelCase
            $node instanceof ClassMethod => $this->validateMethodName($node),
            // Ensure class constants MACRO_CASE
            $node instanceof ClassConst => $this->validateClassConstants($node),
            default => null,
        };
    }

    private function validateClassConstants(ClassConst $classConst): ClassConst
    {
        foreach ($classConst->consts as $const) {
            $constName = $const->name->toString();

            $macroCase = $this->caseConverter->macroCase($constName);

            if ($constName !== $macroCase) {
                $const->name = new Identifier($macroCase);
            }
        }

        return $classConst;
    }

    private function validateClassName(Class_ $class): Class_
    {
        if ($class->name === null) {
            return $class;
        }

        $className = $class->name->toString();

        $pascalCase = $this->caseConverter->pascalCase($className);

        if ($className !== $pascalCase) {
            $class->name = new Identifier($pascalCase);
        }

        return $class;
    }

    private function validateMethodName(ClassMethod $classMethod): ClassMethod
    {
        $methodName = $classMethod->name->toString();

        $isMagicMethod = \str_starts_with($methodName, '__');

        $camelCase = $this->caseConverter->camelCase($methodName);

        if ($methodName !== $camelCase) {
            $classMethod->name = new Identifier($isMagicMethod ? '__' . $camelCase : $camelCase);
        }

        return $classMethod;
    }
}
