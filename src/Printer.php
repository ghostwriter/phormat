<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use PhpParser\PrettyPrinter\Standard;

final class Printer extends Standard
{
    public function __construct()
    {
        parent::__construct([
            'shortArraySyntax' => true,
        ]);
    }

    //    #[Override]
    //    protected function pAttributeGroup(AttributeGroup $node): string
    //    {
    //        return '#[' . $this->pCommaSeparated($node->attrs) . ']';
    //    }
    //    #[Override]
    //    protected function pClassCommon(Class_ $node, $afterClassToken): string
    //    {
    //        return $this->pAttrGroups($node->attrGroups, $node->name === null) . $this->pModifiers($node->flags) . 'class' . $afterClassToken . ($node->extends !== null ? ' extends ' . $this->p($node->extends) : '') . (!empty($node->implements) ? ' implements ' . $this->pCommaSeparated($node->implements) : '') . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' . $this->nl;
    //    }
    //    #[Override]
    //    protected function pExpr_ArrowFunction(ArrowFunction $node): string
    //    {
    //        return $this->pAttrGroups($node->attrGroups, true) . ($node->static ? 'static ' : '') . 'fn ' . ($node->byRef ? '&' : '') . '(' . $this->pCommaSeparated($node->params) . ')' . ($node->returnType !== null ? ': ' . $this->p($node->returnType) : '') . ' => ' . $this->p($node->expr);
    //    }
    //    #[Override]
    //    protected function pExpr_Closure(Closure $node): string
    //    {
    //        return $this->pAttrGroups($node->attrGroups, true) . ($node->static ? 'static ' : '') . 'function ' . ($node->byRef ? '&' : '') . '(' . $this->pCommaSeparated($node->params) . ')' . (!empty($node->uses) ? ' use(' . $this->pCommaSeparated($node->uses) . ')' : '') . ($node->returnType !== null ? ': ' . $this->p($node->returnType) : '') . ' {' . $this->pStmts($node->stmts) . $this->nl . '}';
    //    }
    //    #[Override]
    //    protected function pStmt_ClassMethod(ClassMethod $node): string
    //    {
    //        return sprintf('%s%sfunction %s%s(%s)%s%s', $this->pAttrGroups($node->attrGroups), $this->pModifiers($node->flags), $node->byRef ? '&' : '', $node->name, $this->pMaybeMultiline($node->params), $node->returnType !== null ? ': ' . $this->p($node->returnType) : '', $node->stmts !== null ? $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' : ';');
    //    }
    //    #[Override]
    //    protected function pStmt_Declare(Declare_ $node): string
    //    {
    //        return 'declare(' . $this->pCommaSeparated($node->declares) . ')' . ($node->stmts !== null ? ' {' . $this->pStmts($node->stmts) . $this->nl . '}' : ';') . $this->nl;
    //    }
    //    #[Override]
    //    protected function pStmt_Enum(Enum_ $node): string
    //    {
    //        return $this->pAttrGroups($node->attrGroups) . 'enum ' . $node->name . ($node->scalarType ? ": {$node->scalarType}" : '') . (!empty($node->implements) ? ' implements ' . $this->pCommaSeparated($node->implements) : '') . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' . $this->nl;
    //    }
    //    #[Override]
    //    protected function pStmt_Function(Function_ $node): string
    //    {
    //        return $this->pAttrGroups($node->attrGroups) . 'function ' . ($node->byRef ? '&' : '') . $node->name . '(' . $this->pCommaSeparated($node->params) . ')' . ($node->returnType !== null ? ': ' . $this->p($node->returnType) : '') . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' . $this->nl;
    //    }
    //    protected function pStmt_GroupUse(GroupUse $node): string
    //    {
    //        return 'use ' . $this->pUseType($node->type) . $this->pName($node->prefix) . '\\{' . $this->pCommaSeparated($node->uses) . '};';
    //    }
    //    #[Override]
    //    protected function pStmt_Interface(Interface_ $node): string
    //    {
    //        return $this->pAttrGroups($node->attrGroups) . 'interface ' . $node->name . (!empty($node->extends) ? ' extends ' . $this->pCommaSeparated($node->extends) : '') . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' . $this->nl;
    //    }
    //    #[Override]
    //    protected function pStmt_Trait(Trait_ $node): string
    //    {
    //        return $this->pAttrGroups($node->attrGroups) . 'trait ' . $node->name . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}' . $this->nl;
    //    }
    //    #[Override]
    //    protected function pStmt_Use(Use_ $node): string
    //    {
    //        return 'use ' . $this->pUseType($node->type) . $this->pCommaSeparated($node->uses) . ';';
    //    }
    //    #[Override]
    //    protected function pStmt_UseUse(UseUse $node): string
    //    {
    //        return $this->pUseType($node->type) . $this->p($node->name) . ($node->alias !== null ? ' as ' . $node->alias : '');
    //    }
    //    /**
    //     * Pretty prints an array of nodes (statements) and indents them optionally.
    //     *
    //     * @param Node[] $nodes  Array of nodes
    //     * @param bool   $nodes Whether to indent the printed nodes
    //     *
    //     * @return string Pretty printed statements
    //     */
    //    #[Override]
    //    protected function pStmts(array $nodes, bool $indent = true): string
    //    {
    //        if ($indent) {
    //            $this->indent();
    //        }
    //        $result = '';
    //        foreach ($nodes as $node) {
    //            $comments = $node->getComments();
    //            if ($comments) {
    //                $result .= $this->nl . $this->pComments($comments);
    //                if ($node instanceof Nop) {
    //                    continue;
    //                }
    //            }
    //            $result .= $this->nl . $this->p($node);
    //        }
    //        if ($indent) {
    //            $this->outdent();
    //        }
    //        return $result;
    //    }
    //    public function print(array $nodes): string
    //    {
    //        return $this->prettyPrintFile($nodes);
    //    }
    //
    //    public function printCode(array $nodes): string
    //    {
    //        return $this->prettyPrint($nodes);
    //    }
}
