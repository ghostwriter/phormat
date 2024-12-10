<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Override;
use PhpParser\ErrorHandler;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\NameContext;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TraitUseAdaptation\Precedence;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\UnionType;
use PhpParser\NodeVisitorAbstract;

final class NodeNameResolvingVisitor extends NodeVisitorAbstract
{
    /**
     * @var bool Whether to preserve original names
     */
    private bool $preserveOriginalNames = false;

    /**
     * @var bool Whether to replace resolved nodes in place, or to add resolvedNode attributes
     */
    private bool $replaceNodes = true;

    /**
     * @var NameContext Naming context
     */
    private readonly NameContext $nameContext;

    /**
     * @var NameContext Naming context
     */
    private readonly NameContext $nameContext;

    /**
     * @param null|ErrorHandler $errorHandler Error handler
     */
    public function __construct(?ErrorHandler $errorHandler = null)
    {
        $this->nameContext = new NameContext($errorHandler ?? new Throwing());
    }

    #[Override]
    public function beforeTraverse(array $nodes): void
    {
        $this->nameContext->startNamespace();
    }

    #[Override]
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->nameContext->startNamespace($node->name);
        } elseif ($node instanceof Use_) {
            foreach ($node->uses as $use) {
                $this->addAlias($use, $node->type, null);
            }
        } elseif ($node instanceof GroupUse) {
            foreach ($node->uses as $use) {
                $this->addAlias($use, $node->type, $node->prefix);
            }
        } elseif ($node instanceof Class_) {
            if ($node->extends !== null) {
                $node->extends = $this->resolveClassName($node->extends);
            }

            foreach ($node->implements as &$interface) {
                $interface = $this->resolveClassName($interface);
            }

            $this->resolveAttrGroups($node);
            if ($node->name !== null) {
                $this->addNamespacedName($node);
            }
        } elseif ($node instanceof Interface_) {
            foreach ($node->extends as &$interface) {
                $interface = $this->resolveClassName($interface);
            }

            $this->resolveAttrGroups($node);
            $this->addNamespacedName($node);
        } elseif ($node instanceof Enum_) {
            foreach ($node->implements as &$interface) {
                $interface = $this->resolveClassName($interface);
            }

            $this->resolveAttrGroups($node);
            if ($node->name !== null) {
                $this->addNamespacedName($node);
            }
        } elseif ($node instanceof Trait_) {
            $this->resolveAttrGroups($node);
            $this->addNamespacedName($node);
        } elseif ($node instanceof Function_) {
            $this->resolveSignature($node);
            $this->resolveAttrGroups($node);
            $this->addNamespacedName($node);
        } elseif ($node instanceof ClassMethod || $node instanceof Closure || $node instanceof ArrowFunction) {
            $this->resolveSignature($node);
            $this->resolveAttrGroups($node);
        } elseif ($node instanceof Property) {
            if ($node->type !== null) {
                $node->type = $this->resolveType($node->type);
            }

            $this->resolveAttrGroups($node);
        } elseif ($node instanceof Const_) {
            foreach ($node->consts as $const) {
                $this->addNamespacedName($const);
            }
        } elseif ($node instanceof ClassConst) {
            if ($node->type !== null) {
                $node->type = $this->resolveType($node->type);
            }

            $this->resolveAttrGroups($node);
        } elseif ($node instanceof EnumCase) {
            $this->resolveAttrGroups($node);
        } elseif ($node instanceof StaticCall || $node instanceof StaticPropertyFetch || $node instanceof ClassConstFetch || $node instanceof New_ || $node instanceof Instanceof_) {
            if ($node->class instanceof Name) {
                $node->class = $this->resolveClassName($node->class);
            }
        } elseif ($node instanceof Catch_) {
            foreach ($node->types as &$type) {
                $type = $this->resolveClassName($type);
            }
        } elseif ($node instanceof FuncCall) {
            if ($node->name instanceof Name) {
                $node->name = $this->resolveName($node->name, Use_::TYPE_FUNCTION);
            }
        } elseif ($node instanceof ConstFetch) {
            $node->name = $this->resolveName($node->name, Use_::TYPE_CONSTANT);
        } elseif ($node instanceof TraitUse) {
            foreach ($node->traits as &$trait) {
                $trait = $this->resolveClassName($trait);
            }

            foreach ($node->adaptations as $adaptation) {
                if ($adaptation->trait !== null) {
                    $adaptation->trait = $this->resolveClassName($adaptation->trait);
                }

                if ($adaptation instanceof Precedence) {
                    foreach ($adaptation->insteadof as &$insteadof) {
                        $insteadof = $this->resolveClassName($insteadof);
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get name resolution context.
     */
    public function getNameContext(): NameContext
    {
        return $this->nameContext;
    }

    private function addAlias(UseUse $useUse, int $type, ?Name $prefix = null): void
    {
        // Add prefix for group uses
        $name = $prefix instanceof Name ? Name::concat($prefix, $useUse->name) : $useUse->name;
        // Type is determined either by individual element or whole use declaration
        $type |= $useUse->type;
        $this->nameContext->addAlias($name, (string) $useUse->getAlias(), $type, $useUse->getAttributes());
    }

    private function addAlias(UseUse $useUse, int $type, ?Name $prefix = null): void
    {
        // Add prefix for group uses
        $name = $prefix instanceof Name ? Name::concat($prefix, $useUse->name) : $useUse->name;
        // Type is determined either by individual element or whole use declaration
        $type |= $useUse->type;
        $this->nameContext->addAlias($name, (string) $useUse->getAlias(), $type, $useUse->getAttributes());
    }

    private function addNamespacedName(Node $node): void
    {
        $node->namespacedName = Name::concat($this->nameContext->getNamespace(), $node->name->toString());
    }

    private function resolveAttrGroups(Node $node): void
    {
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                $attr->name = $this->resolveClassName($attr->name);
            }
        }
    }

    private function resolveClassName(Name $name): Name
    {
        return $this->resolveName($name, Use_::TYPE_NORMAL);
    }

    /**
     * Resolve name, according to name resolver options.
     *
     * @param Name $name Function or constant name to resolve
     * @param int  $type One of \PhpParser\Node\Stmt\Use_::TYPE_*
     *
     * @return Name Resolved name, or original name with attribute
     */
    private function resolveName(Name $name, int $type): Name
    {
        if (! $this->replaceNodes) {
            $resolvedName = $this->nameContext->getResolvedName($name, $type);
            if ($resolvedName !== null) {
                $name->setAttribute('resolvedName', $resolvedName);
            } else {
                $name->setAttribute(
                    'namespacedName',
                    FullyQualified::concat($this->nameContext->getNamespace(), $name, $name->getAttributes())
                );
            }

            return $name;
        }

        if ($this->preserveOriginalNames) {
            // Save the original name
            $originalName = $name;
            $name = clone $originalName;
            $name->setAttribute('originalName', $originalName);
        }

        $resolvedName = $this->nameContext->getResolvedName($name, $type);
        if ($resolvedName !== null) {
            return $resolvedName;
        }

        // unqualified names inside a namespace cannot be resolved at compile-time
        // add the namespaced version of the name as an attribute
        $name->setAttribute(
            'namespacedName',
            FullyQualified::concat($this->nameContext->getNamespace(), $name, $name->getAttributes())
        );
        return $name;
    }

    /**
     * @param ClassMethod|Closure|Function_ $node
     */
    private function resolveSignature(ArrowFunction|ClassMethod|Closure|Function_ $node): void
    {
        foreach ($node->params as $param) {
            $param->type = $this->resolveType($param->type);
            $this->resolveAttrGroups($param);
        }

        $node->returnType = $this->resolveType($node->returnType);
    }

    private function resolveType($node)
    {
        if ($node instanceof Name) {
            return $this->resolveClassName($node);
        }

        if ($node instanceof NullableType) {
            $node->type = $this->resolveType($node->type);
            return $node;
        }

        if ($node instanceof UnionType || $node instanceof IntersectionType) {
            foreach ($node->types as &$type) {
                $type = $this->resolveType($type);
            }

            return $node;
        }

        return $node;
    }
}
