<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Ghostwriter\Phormat\Interface\FormatterInterface;
use Override;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Concat;
use PhpParser\Node\Expr\AssignOp\Plus;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Concat as PhpParserConcat;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Isset_;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\MatchArm;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\EnumCase;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\StaticVar;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\TraitUseAdaptation\Alias;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\Unset_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\PrettyPrinter\Standard;

use function strnatcasecmp;

abstract class AbstractNodeVisitor implements FormatterInterface, NodeVisitor
{
    /**
     * If NodeVisitor::enterNode() or NodeVisitor::leaveNode() returns
     * STOP_TRAVERSAL, traversal is aborted.
     *
     * The afterTraverse() method will still be invoked.
     */
    public const int STOP_TRAVERSAL = NodeTraverser::STOP_TRAVERSAL;

    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CHILDREN, child nodes
     * of the current node will not be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will still be called on the current
     * node and leaveNode() will also be invoked for the current node.
     */
    public const int DONT_TRAVERSE_CHILDREN = NodeTraverser::DONT_TRAVERSE_CHILDREN;

    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CURRENT_AND_CHILDREN, child nodes
     * of the current node will not be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will not be called as well.
     * leaveNode() will be invoked for visitors that has enterNode() method invoked.
     */
    public const int DONT_TRAVERSE_CURRENT_AND_CHILDREN = NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;

    /**
     * If NodeVisitor::leaveNode() returns REMOVE_NODE for a node that occurs
     * in an array, it will be removed from the array.
     *
     * For subsequent visitors leaveNode() will still be invoked for the
     * removed node.
     */
    public const int REMOVE_NODE = NodeTraverser::REMOVE_NODE;

    public const null NEXT_NODE = null;

    private const int EQUAL = 0;

    private const int LEFT_BEFORE_RIGHT = -1;

    private const int RIGHT_BEFORE_LEFT = 1;

    public function __construct(
        private readonly Standard $standard
    ) {
    }

    public function accepts(Node $node): bool
    {
        return true;
    }

    public function after(array $nodes): ?array
    {
        return $nodes;
    }

    /**
     * Called once after traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return null|Node[] Array of nodes
     */
    #[Override]
    public function afterTraverse(array $nodes): ?array
    {
        return self::NEXT_NODE;
    }

    public function before(array $nodes): ?array
    {
        return $nodes;
    }

    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  * null:      $nodes stays as-is
     *  * otherwise: $nodes is set to the return value
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return null|Node[] Array of nodes
     */
    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        $this->format(...$nodes);
        return $nodes;
    }

    final public function compare(Node $left, Node $right): int
    {
        $left = $this->sort($left);
        $right = $this->sort($right);
        return match (true) {
            $left instanceof ElseIf_ => match (true) {
                $right instanceof ElseIf_ => $this->compareElseIf($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof StaticVar => match (true) {
                $right instanceof StaticVar => $this->compareStaticVar($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Arg => match (true) {
                $right instanceof Arg => $this->compareArg($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            //            $left instanceof Expr => match (true) {
            ////                $right instanceof Expr => $this->compareExpr($left, $right),
            //                default => die(var_dump([$left::class, $right::class, __LINE__, __FUNCTION__)),
            //            },
            $left instanceof Foreach_ => match (true) {
                $right instanceof Expression, $right instanceof Return_ => 0,
                $right instanceof Foreach_ => $this->compareForeach($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof TryCatch => match (true) {
                $right instanceof If_ => 0,
                $right instanceof TryCatch => $this->compareTryCatch($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof AttributeGroup => match (true) {
                $right instanceof AttributeGroup => $this->compareAttributeGroup($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof ClassConst => match (true) {
                $right instanceof ClassConst => $this->compareClassConst($left, $right),
                $right instanceof ClassMethod => self::LEFT_BEFORE_RIGHT,
                $right instanceof Property => self::LEFT_BEFORE_RIGHT,
                $right instanceof TraitUse => self::RIGHT_BEFORE_LEFT,
                $right instanceof Nop => 0,
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof ClassConstFetch => match (true) {
                $right instanceof ClassConstFetch => $this->compareClassConstFetch($left, $right),
                $right instanceof Ternary => 0,
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof ClassMethod => match (true) {
                $right instanceof ClassConst => self::RIGHT_BEFORE_LEFT,
                $right instanceof EnumCase => self::RIGHT_BEFORE_LEFT,
                $right instanceof TraitUse => self::RIGHT_BEFORE_LEFT,
                $right instanceof Property => self::RIGHT_BEFORE_LEFT,
                $right instanceof ClassMethod => $this->compareClassMethod($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Class_ => match (true) {
                $right instanceof Class_ => $this->compareClass($left, $right),
                $right instanceof Interface_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Enum_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Trait_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Use_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Expression, $right instanceof Function_, $right instanceof If_, $right instanceof Nop => 0,
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof EnumCase => match (true) {
                $right instanceof ClassMethod => self::LEFT_BEFORE_RIGHT,
                $right instanceof EnumCase => $this->compareEnumCase($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Enum_ => match (true) {
                $right instanceof Class_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Use_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Trait_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Interface_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Enum_ => $this->compareEnum($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Expression => match (true) {
                $right instanceof Continue_, $right instanceof For_, $right instanceof Foreach_, $right instanceof TryCatch, $right instanceof Class_, $right instanceof Trait_, $right instanceof If_, $right instanceof Nop, $right instanceof Return_, $right instanceof Use_ => 0,
                $right instanceof Expression => $this->compareExpression($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof FuncCall => match (true) {
                $right instanceof FuncCall => $this->compareFuncCall($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof UseUse => match (true) {
                $right instanceof UseUse => $this->compareUseUse($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Function_ => match (true) {
                $right instanceof Class_, $right instanceof If_, $right instanceof Use_ => 0,
                $right instanceof Function_ => $this->compareFunction($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Identical, $left instanceof String_, $left instanceof Return_, $left instanceof PropertyFetch, $left instanceof Ternary, $left instanceof Param => 0,
            $left instanceof If_ => match (true) {
                $right instanceof Throw_, $right instanceof Return_, $right instanceof Class_, $right instanceof Expression, $right instanceof Function_, $right instanceof If_, $right instanceof Nop, $right instanceof Use_ => 0,
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Interface_ => match (true) {
                $right instanceof Class_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Enum_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Trait_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Use_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Interface_ => $this->compareInterface($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Static_ => match (true) {
                $right instanceof If_, $right instanceof Expression => 0,
                $right instanceof Static_ => $this->compareStatic($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Instanceof_ => match (true) {
                $right instanceof Identical => 0,
                $right instanceof Instanceof_ => $this->compareInstanceof($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Alias => match (true) {
                $right instanceof Alias => $this->compareTraitUseAdaptationAlias($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof MatchArm => match (true) {
                $right instanceof MatchArm => $this->compareMatchArm($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Name => match (true) {
                $right instanceof Name => $this->compareName($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof GroupUse => match (true) {
                $right instanceof GroupUse => $this->compareGroupUse($left, $right),
                $right instanceof Class_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Use_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Enum_ => self::LEFT_BEFORE_RIGHT,
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Property => match (true) {
                $right instanceof ClassConst => self::RIGHT_BEFORE_LEFT,
                $right instanceof ClassMethod => self::LEFT_BEFORE_RIGHT,
                $right instanceof TraitUse => self::RIGHT_BEFORE_LEFT,
                $right instanceof Property => $this->compareProperty($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof TraitUse => match (true) {
                $right instanceof ClassConst, $right instanceof ClassMethod, $right instanceof Property => self::LEFT_BEFORE_RIGHT,
                $right instanceof TraitUse => $this->compareTraitUse($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Trait_ => match (true) {
                $right instanceof Use_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Class_ => self::RIGHT_BEFORE_LEFT,
                $right instanceof Enum_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Trait_ => $this->compareTrait($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Use_ => match (true) {
                $right instanceof GroupUse => self::LEFT_BEFORE_RIGHT,
                $right instanceof Class_, $right instanceof Enum_, $right instanceof Interface_, $right instanceof Trait_ => self::LEFT_BEFORE_RIGHT,
                $right instanceof Expression, $right instanceof Function_, $right instanceof If_, $right instanceof Nop => 0,
                $right instanceof Use_ => $this->compareUse($left, $right),
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            $left instanceof Nop => match (true) {
                $right instanceof ClassConst, $right instanceof Expression, $right instanceof Class_, $right instanceof If_, $right instanceof Use_ => 0,
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            },
            default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
        };
    }

    final public function compareAttributeGroup(AttributeGroup $left, AttributeGroup $right): int
    {
        return $this->compareNode($left, $right);
    }

    final public function compareClass(Class_ $left, Class_ $right): int
    {
        return $this->compareName($left, $right);
    }

    final public function compareClassConst(ClassConst $left, ClassConst $right): int
    {
        return $this->compareNode($left, $right);
        //        return $this->getName($left) <=> $this->getName($right);
    }

    final public function compareClassConstFetch(ClassConstFetch $left, ClassConstFetch $right): int
    {
        return $this->compareNode($left, $right);
    }

    final public function compareClassLike(ClassLike $left, ClassLike $right): int
    {
        return $this->compareName($left, $right);
        //        $this->sort($left);
        return \strnatcasecmp($this->getName($left), $this->getName($right));
    }

    final public function compareClassMethod(ClassMethod $left, ClassMethod $right): int
    {
        return $this->getName($left) <=> $this->getName($right);
    }

    final public function compareEnum(Enum_ $left, Enum_ $right): int
    {
        return $this->compareName($left, $right);
    }

    final public function compareEnumCase(EnumCase $left, EnumCase $right): int
    {
        return $this->getName($left) <=> $this->getName($right);
        //        die(var_dump([[
        //            $this->getName($left) => $this->getName($right),
        //        ], strnatcasecmp($this->getName($left), $this->getName($right)), $this->getName($left) <=> $this->getName(
        //            $right
        //        ));
        return $this->compareNode($left, $right);
    }

    final public function compareExpr(Expr $left, Expr $right): int
    {
        return \strnatcasecmp(
            (string) $this->standard->prettyPrintExpr($left),
            (string) $this->standard->prettyPrintExpr($right)
        );
    }

    final public function compareExpression(Expression $left, Expression $right): int
    {
        return $this->compareNode($left, $right);
    }

    final public function compareForeach(Foreach_ $left, Foreach_ $right): int
    {
        return 0;
    }

    final public function compareFuncCall(FuncCall $left, FuncCall $right): int
    {
        return 0;
        // $this->compareNode($left, $right);
    }

    final public function compareFunction(Function_ $left, Function_ $right): int
    {
        return $this->compareName($left, $right);
    }

    final public function compareGroupUse(GroupUse $left, GroupUse $right): int
    {
        return $this->compareNode($left, $right);
        return \strnatcasecmp(
            (string) $this->standard->prettyPrint([$this->sortGroupUse($left)]),
            (string) $this->standard->prettyPrint([$this->sortGroupUse($right)])
        );
    }

    final public function compareIdentical(Identical $left, Identical $right): int
    {
        return 0;
        // $this->compareNode($left, $right);
    }

    final public function compareIf(If_ $left, If_ $right): int
    {
        return 0;
    }

    final public function compareInstanceof(Instanceof_ $left, Instanceof_ $right): int
    {
        return 0;
        //        $this->compareNode($left, $right);
    }

    final public function compareInterface(Interface_ $left, Interface_ $right): int
    {
        return $this->compareName($left, $right);
    }

    final public function compareMatchArm(MatchArm $left, MatchArm $right): int
    {
        return $this->compareNode($left, $right);
    }

    final public function compareName(Node $left, Node $right): int
    {
        return \strnatcasecmp($this->getName($left), $this->getName($right));
    }

    final public function compareNode(Node $left, Node $right): int
    {
        return \strnatcasecmp(
            (string) $this->standard->prettyPrint([$left]),
            (string) $this->standard->prettyPrint([$right])
        );
    }

    final public function compareProperty(Property $left, Property $right): int
    {
        return $this->compareName($left, $right);
    }

    final public function compareStatic(Static_ $left, Static_ $right): int
    {
        return 0;
    }

    final public function compareTrait(Trait_ $left, Trait_ $right): int
    {
        return $this->compareNode($left, $right);
    }

    final public function compareTraitUse(TraitUse $left, TraitUse $right): int
    {
        return $this->compareNode($left, $right);
    }

    final public function compareTryCatch(TryCatch $left, TryCatch $right): int
    {
        return 0;
    }

    final public function compareUse(Use_ $left, Use_ $right): int
    {
        return match ($left->type) {
            default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
            Use_::TYPE_CONSTANT => match ($right->type) {
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
                Use_::TYPE_CONSTANT => $this->compareNode($left, $right),
                Use_::TYPE_FUNCTION => self::LEFT_BEFORE_RIGHT,
                Use_::TYPE_NORMAL => self::RIGHT_BEFORE_LEFT,
            },
            Use_::TYPE_FUNCTION => match ($right->type) {
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
                Use_::TYPE_CONSTANT => self::RIGHT_BEFORE_LEFT,
                Use_::TYPE_NORMAL => self::RIGHT_BEFORE_LEFT,
                Use_::TYPE_FUNCTION => $this->compareNode($left, $right),
            },
            Use_::TYPE_NORMAL => match ($right->type) {
                default => die(\var_dump([$left::class, $right::class, __LINE__, __FUNCTION__])),
                Use_::TYPE_CONSTANT => self::LEFT_BEFORE_RIGHT,
                Use_::TYPE_FUNCTION => self::LEFT_BEFORE_RIGHT,
                Use_::TYPE_NORMAL => $this->compareNode($left, $right),
            },
        };
        return $this->compareNode($this->sort($left), $this->sort($right));
    }

    public function enter(Node $node): null|int|Node
    {
        return $node;
    }

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::DONT_TRAVERSE_CHILDREN
     *        => Children of $node are not traversed. $node stays as-is
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * otherwise
     *        => $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|int|Node Replacement node (or special return value)
     */
    #[Override]
    public function enterNode(Node $node): null|int|Node
    {
        if ($this->skip($node)) {
            return self::NEXT_NODE;
        }

        $this->format($node);
        return $node;
    }

    final public function getName(Node $node): string
    {
        return match (true) {
            $node instanceof ClassConst => $node->consts[0]->name->toString(),
            $node instanceof ClassMethod => $node->name->toString(),
            $node instanceof Class_, $node instanceof Trait_, $node instanceof Interface_, $node instanceof Enum_ => $node->name?->toString(),
            $node instanceof EnumCase => $node->name->toString(),
            $node instanceof FunctionLike => $this->standard->print([$node]),
            $node instanceof Function_ => $node->name->toString(),
            $node instanceof Name => $node->toString(),
            $node instanceof Namespace_ => $node->name?->toString() ?? '',
            $node instanceof Property => $node->props[0]->name->toString(),
            $node instanceof TraitUse => $node->traits[0]->toString(),
            $node instanceof UseUse => $node->name->toString(),
            default => die(\var_dump([__LINE__, __FUNCTION__, $node::class])),
        };
    }

    public function leave(Node $node): null|array|int|Node
    {
        return $node;
    }

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  * null
     *        => $node stays as-is
     *  * NodeTraverser::REMOVE_NODE
     *        => $node is removed from the parent array
     *  * NodeTraverser::STOP_TRAVERSAL
     *        => Traversal is aborted. $node stays as-is
     *  * array (of Nodes)
     *        => The return value is merged into the parent array (at the position of the $node)
     *  * otherwise
     *        => $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|int|Node|Node[] Replacement node (or special return value)
     */
    #[Override]
    public function leaveNode(Node $node): null|array|int|Node
    {
        if ($node->getAttribute('phormat.remove', false)) {
            return self::REMOVE_NODE;
        }

        return self::NEXT_NODE;
    }

    public function matches(Node $node): bool
    {
        return true;
    }

    final public function remove(array $nodes): Node
    {
        foreach ($nodes as $node) {
            $node->setAttribute('phormat.remove', true);
        }

        return $node;
    }

    final public function removeNode(Node $node): Node
    {
        $node->setAttribute('phormat.remove', true);
        return $node;
    }

    public function skip(Node $node): bool
    {
        return true;
    }

    final public function sort(Node $node): Node
    {
        return match (true) {
            //            $node instanceof PostDec => $this->sortPostDec($node),
            //            $node instanceof Break_ => $this->sortBreak($node),
            //            $node instanceof Concat => $this->sortAssignOpConcat($node),
            //            $node instanceof Encapsed => $this->sortEncapsed($node),
            //            $node instanceof For_ => $this->sortFor($node),
            //            $node instanceof SmallerOrEqual => $this->sortBinaryOpSmallerOrEqual($node),
            //            $node instanceof PostInc => $this->sortPostInc($node),
            //            $node instanceof Echo_ => $this->sortEcho($node),
            //            $node instanceof NullableType => $this->sortNullableType($node),
            //            $node instanceof NotIdentical => $this->sortBinaryOpNotIdentical($node),
            //            $node instanceof Unset_ => $this->sortUnset($node),
            //            $node instanceof Const_ => $this->sortConst($node),
            //            $node instanceof Expr\Cast\Array_ => $this->sortCastArray($node),
            //            $node instanceof Greater => $this->sortBinaryOpGreater($node),
            //            $node instanceof ErrorSuppress => $this->sortErrorSuppress($node),
            //            $node instanceof Expr\Cast\String_ => $this->sortCastString($node),
            //            $node instanceof Smaller => $this->sortBinaryOpSmaller($node),
            //            $node instanceof ElseIf_ => $this->sortElseIf($node),
            //            $node instanceof Int_ => $this->sortCastInt($node),
            //            $node instanceof Expr\BinaryOp\Plus => $this->sortBinaryOpPlus($node),
            //            $node instanceof UnaryMinus => $this->sortUnaryMinus($node),
            //            $node instanceof Plus => $this->sortAssignOpPlus($node),
            //            $node instanceof Minus => $this->sortBinaryOpMinus($node),
            //            $node instanceof Else_ => $this->sortElse($node),
            //            $node instanceof Dir => $this->sortDir($node),
            //            $node instanceof Array_ => $this->sortArray($node),
            //            $node instanceof Assign => $this->sortAssign($node),
            //            $node instanceof ArrayItem => $this->sortArrayItem($node),
            //            $node instanceof File => $this->sortFile($node),
            //            $node instanceof Attribute => $this->sortAttribute($node),
            //            $node instanceof BinaryOpConcat => $this->sortBinaryOpConcat($node),
            //            $node instanceof New_ => $this->sortNew($node),
            $node instanceof PropertyProperty => $this->sortPropertyProperty($node),
            //            $node instanceof BooleanNot => $this->sortBooleanNot($node),
            //            $node instanceof Equal => $this->sortBinaryOpEqual($node),
            //            $node instanceof Catch_ => $this->sortCatch($node),
            $node instanceof ArrayDimFetch => $this->sortArrayDimFetch($node),
            //            $node instanceof Isset_ => $this->sortIsset($node),
            //            $node instanceof Coalesce => $this->sortCoalesce($node),
            //            $node instanceof Throw_ => $this->sortThrow($node),
            //            $node instanceof StaticCall => $this->sortStaticCall($node),
            //            $node instanceof Exit_ => $this->sortExit($node),
            //            $node instanceof ConstFetch => $this->sortConstFetch($node),
            $node instanceof Arg => $this->sortArg($node),
            //            $node instanceof Clone_ => $this->sortClone($node),
            //            $node instanceof BooleanOr => $this->sortBinaryOpBooleanOr($node),
            //            $node instanceof Continue_ => $this->sortContinue($node),
            //            $node instanceof GreaterOrEqual => $this->sortBinaryOpGreaterOrEqual($node),
            //            $node instanceof BooleanAnd => $this->sortBooleanAnd($node),
            //            $node instanceof ClosureUse => $this->sortClosureUse($node),
            //            $node instanceof Include_ => $this->sortInclude($node),
            //            $node instanceof Variable => $this->sortVariable($node),
            //            $node instanceof Closure => $this->sortClosure($node),
            //            $node instanceof LNumber => $this->sortLNumber($node),
            //            $node instanceof Identifier => $this->sortIdentifier($node),
            //            $node instanceof DeclareDeclare => $this->sortDeclareDeclare($node),
            //            $node instanceof Declare_ => $this->sortDeclare($node),
            //            $node instanceof InlineHTML => $this->sortInlineHTML($node),
            //            $node instanceof Foreach_ => $this->sortForeach($node),
            //            $node instanceof TryCatch => $this->sortTryCatch($node),
            $node instanceof ClassConst => $this->sortClassConst($node),
            $node instanceof ClassMethod => $this->sortClassMethod($node),
            $node instanceof Class_ => $this->sortClass($node),
            $node instanceof EnumCase => $this->sortEnumCase($node),
            $node instanceof Enum_ => $this->sortEnum($node),
            //            $node instanceof Expr => $this->sortExpr($node),
            //            $node instanceof Expression => $this->sortExpression($node),
            $node instanceof FuncCall => $this->sortFuncCall($node),
            //            $node instanceof While_ => $this->sortWhile($node),
            $node instanceof Function_ => $this->sortFunction($node),
            //            $node instanceof Identical => $this->sortIdentical($node),
            //            $node instanceof If_ => $this->sortIf($node),
            $node instanceof Instanceof_ => $this->sortInstanceof($node),
            $node instanceof Interface_ => $this->sortInterface($node),
            $node instanceof MatchArm => $this->sortMatchArm($node),
            $node instanceof Match_ => $this->sortMatch($node),
            $node instanceof MethodCall => $this->sortMethodCall($node),
            //            $node instanceof Name => $this->sortName($node),
            //            $node instanceof Nop => $this->sortNop($node),
            $node instanceof Param => $this->sortParam($node),
            $node instanceof Property => $this->sortProperty($node),
            $node instanceof PropertyFetch => $this->sortPropertyFetch($node),
            //            $node instanceof Return_ => $this->sortReturn($node),
            //            $node instanceof String_ => $this->sortString($node),
            $node instanceof Ternary => $this->sortTernary($node),
            $node instanceof TraitUse => $this->sortTraitUse($node),
            $node instanceof Trait_ => $this->sortTrait($node),
            $node instanceof Use_ => $this->sortUse($node),
            $node instanceof Static_ => $this->sortStatic($node),
            //            $node instanceof ClassLike => $this->sortClassLike($node),
            $node instanceof ClassConstFetch => $this->sortClassConstFetch($node),
            $node instanceof GroupUse => $this->sortGroupUse($node),
            $node instanceof AttributeGroup => $this->sortAttributeGroup($node),
            $node instanceof Namespace_ => $this->sortNamespace($node),
            $node instanceof UseUse => $this->sortUseUse($node),
            default => $node,
        };
    }

    final public function sortAttributeGroup(AttributeGroup $attributeGroup): AttributeGroup
    {
        \usort($attributeGroup->attrs, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $attributeGroup;
    }

    final public function sortClass(Class_ $class): Class_
    {
        \usort($class->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($class->implements, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($class->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $class;
    }

    public function sortClassConst(ClassConst $classConst): ClassConst
    {
        \usort($classConst->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($classConst->consts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $classConst;
    }

    final public function sortClassConstFetch(ClassConstFetch $classConstFetch): ClassConstFetch
    {
        return $classConstFetch;
    }

    final public function sortClassLike(ClassLike $classLike): ClassLike
    {
        \usort($classLike->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($classLike->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $classLike;
    }

    final public function sortClassMethod(ClassMethod $classMethod): ClassMethod
    {
        \usort($classMethod->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($classMethod->params, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $classMethod;
    }

    final public function sortEnum(Enum_ $enum): Enum_
    {
        \usort($enum->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($enum->implements, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($enum->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $enum;
    }

    final public function sortEnumCase(EnumCase $enumCase): EnumCase
    {
        \usort($enumCase->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $enumCase;
    }

    final public function sortExpression(Expression $expression): Expression
    {
        return $expression;
    }

    final public function sortForeach(Foreach_ $foreach): Foreach_
    {
        \usort($foreach->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $foreach;
    }

    final public function sortFuncCall(FuncCall $funcCall): FuncCall
    {
        \usort($funcCall->args, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $funcCall;
    }

    final public function sortFunction(Function_ $function): Function_
    {
        \usort($function->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($function->params, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($function->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $function;
    }

    final public function sortGroupUse(GroupUse $groupUse): GroupUse
    {
        \usort($groupUse->uses, fn (UseUse $left, UseUse $right): int => $this->compare($left, $right));
        return $groupUse;
    }

    final public function sortIdentical(Identical $identical): Identical
    {
        return $identical;
    }

    final public function sortIf(If_ $if): If_
    {
        \usort($if->elseifs, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($if->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $if;
    }

    final public function sortInstanceof(Instanceof_ $instanceof): Instanceof_
    {
        return $instanceof;
    }

    final public function sortInterface(Interface_ $interface): Interface_
    {
        \usort($interface->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($interface->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $interface;
    }

    final public function sortMatch(Match_ $match): Match_
    {
        \usort($match->arms, fn (MatchArm $left, MatchArm $right): int => $this->compare($left, $right));
        return $match;
    }

    final public function sortMatchArm(MatchArm $matchArm): MatchArm
    {
        if ($matchArm->conds === null) {
            return $matchArm;
        }

        \usort($matchArm->conds, fn (Expr $left, Expr $right): int => $this->compare($left, $right));
        return $matchArm;
    }

    final public function sortMethodCall(MethodCall $methodCall): MethodCall
    {
        \usort($methodCall->args, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $methodCall;
    }

    final public function sortName(Name $name): Name
    {
        return $name;
    }

    final public function sortNamespace(Namespace_ $namespace): Namespace_
    {
        \usort($namespace->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $namespace;
    }

    final public function sortNop(Nop $nop): Nop
    {
        return $nop;
    }

    final public function sortParam(Param $param): Param
    {
        \usort($param->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $param;
    }

    final public function sortProperty(Property $property): Property
    {
        \usort($property->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($property->props, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $property;
    }

    final public function sortPropertyFetch(PropertyFetch $propertyFetch): PropertyFetch
    {
        return $propertyFetch;
    }

    final public function sortReturn(Return_ $return): Return_
    {
        return $return;
    }

    final public function sortStatic(Static_ $static): Static_
    {
        \usort($static->vars, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $static;
    }

    final public function sortString(String_ $string): String_
    {
        return $string;
    }

    final public function sortTernary(Ternary $ternary): Ternary
    {
        return $ternary;
    }

    final public function sortTrait(Trait_ $trait): Trait_
    {
        \usort($trait->attrGroups, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($trait->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $trait;
    }

    final public function sortTraitUse(TraitUse $traitUse): TraitUse
    {
        \usort($traitUse->adaptations, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($traitUse->traits, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $traitUse;
    }

    final public function sortTryCatch(TryCatch $tryCatch): TryCatch
    {
        \usort($tryCatch->stmts, fn (Node $left, Node $right): int => $this->compare($left, $right));
        \usort($tryCatch->catches, fn (Node $left, Node $right): int => $this->compare($left, $right));
        return $tryCatch;
    }

    final public function sortUse(Use_ $use): Use_
    {
        \usort($use->uses, fn (UseUse $left, UseUse $right): int => $this->compare($left, $right));
        return $use;
    }

    final public function sortUseUse(UseUse $useUse): UseUse
    {
        return $useUse;
    }

    public function supports(Node $node): bool
    {
        return true;
    }

    private function compareArg(Arg $left, Arg $right): int
    {
        return 0;
    }

    private function compareElseIf(ElseIf_ $left, ElseIf_ $right): int
    {
        return 0;
    }

    private function compareStaticVar(StaticVar $left, StaticVar $right): int
    {
        return 0;
    }

    private function compareTraitUseAdaptationAlias(Alias $left, Alias $right): int
    {
        return 0;
    }

    private function compareUseUse(UseUse $left, UseUse $right): int
    {
        return $this->compareNode($left, $right);
    }

    private function sortArg(Arg $arg): Arg
    {
        return $arg;
    }

    private function sortArray(Array_ $array): Array_
    {
        return $array;
    }

    private function sortArrayDimFetch(ArrayDimFetch $arrayDimFetch): ArrayDimFetch
    {
        return $arrayDimFetch;
    }

    private function sortArrayItem(ArrayItem $arrayItem): ArrayItem
    {
        return $arrayItem;
    }

    private function sortAssign(Assign $assign): Assign
    {
        return $assign;
    }

    private function sortAssignOpConcat(Concat $concat): Concat
    {
        return $concat;
    }

    private function sortAssignOpPlus(Plus $plus): Plus
    {
        return $plus;
    }

    private function sortAttribute(Attribute $attribute): Attribute
    {
        return $attribute;
    }

    private function sortBinaryOpBooleanOr(BooleanOr $booleanOr): BooleanOr
    {
        return $booleanOr;
    }

    private function sortBinaryOpConcat(PhpParserConcat $phpParserConcat): PhpParserConcat
    {
        return $phpParserConcat;
    }

    private function sortBinaryOpEqual(Equal $equal): Equal
    {
        return $equal;
    }

    private function sortBinaryOpGreater(Greater $greater): Greater
    {
        return $greater;
    }

    private function sortBinaryOpGreaterOrEqual(GreaterOrEqual $greaterOrEqual): GreaterOrEqual
    {
        return $greaterOrEqual;
    }

    private function sortBinaryOpMinus(Minus $minus): Minus
    {
        return $minus;
    }

    private function sortBinaryOpNotIdentical(NotIdentical $notIdentical): NotIdentical
    {
        return $notIdentical;
    }

    private function sortBinaryOpPlus(Plus $plus): Plus
    {
        return $plus;
    }

    private function sortBinaryOpSmaller(Smaller $smaller): Smaller
    {
        return $smaller;
    }

    private function sortBinaryOpSmallerOrEqual(SmallerOrEqual $smallerOrEqual): SmallerOrEqual
    {
        return $smallerOrEqual;
    }

    private function sortBooleanAnd(BooleanAnd $booleanAnd): BooleanAnd
    {
        return $booleanAnd;
    }

    private function sortBooleanNot(BooleanNot $booleanNot): BooleanNot
    {
        return $booleanNot;
    }

    private function sortBreak(Break_ $break): Break_
    {
        return $break;
    }

    private function sortCastArray(Array_ $array): Array_
    {
        return $array;
    }

    private function sortCastInt(Int_ $int): Int_
    {
        return $int;
    }

    private function sortCastString(String_ $string): String_
    {
        return $string;
    }

    private function sortCatch(Catch_ $catch): Catch_
    {
        return $catch;
    }

    private function sortClone(Clone_ $clone): Clone_
    {
        return $clone;
    }

    private function sortClosure(Closure $node): Closure
    {
        return $node;
    }

    private function sortClosureUse(ClosureUse $node): ClosureUse
    {
        return $node;
    }

    private function sortCoalesce(Coalesce $coalesce): Coalesce
    {
        return $coalesce;
    }

    private function sortConst(Const_ $const): Const_
    {
        return $const;
    }

    private function sortConstFetch(ConstFetch $constFetch): ConstFetch
    {
        return $constFetch;
    }

    private function sortContinue(Continue_ $continue): Continue_
    {
        return $continue;
    }

    private function sortDeclare(Declare_ $declare): Declare_
    {
        return $declare;
    }

    private function sortDeclareDeclare(DeclareDeclare $declareDeclare): DeclareDeclare
    {
        return $declareDeclare;
    }

    private function sortDir(Dir $dir): Dir
    {
        return $dir;
    }

    private function sortEcho(Echo_ $echo): Echo_
    {
        return $echo;
    }

    private function sortElse(Else_ $else): Else_
    {
        return $else;
    }

    private function sortElseIf(ElseIf_ $elseIf): ElseIf_
    {
        return $elseIf;
    }

    private function sortEncapsed(Encapsed $encapsed): Encapsed
    {
        return $encapsed;
    }

    private function sortErrorSuppress(ErrorSuppress $errorSuppress): ErrorSuppress
    {
        return $errorSuppress;
    }

    private function sortExit(Exit_ $exit): Exit_
    {
        return $exit;
    }

    private function sortExpr(Expr $expr): Expr
    {
        die(\var_dump([$expr::class]));
        return $expr;
    }

    private function sortFile(File $file): File
    {
        return $file;
    }

    private function sortFor(For_ $for): For_
    {
        return $for;
    }

    private function sortIdentifier(Identifier $identifier): Identifier
    {
        return $identifier;
    }

    private function sortInclude(Include_ $include): Include_
    {
        return $include;
    }

    private function sortInlineHTML(InlineHTML $inlineHTML): InlineHTML
    {
        return $inlineHTML;
    }

    private function sortIsset(Isset_ $isset): Isset_
    {
        return $isset;
    }

    private function sortLNumber(LNumber $lNumber): LNumber
    {
        return $lNumber;
    }

    private function sortNew(New_ $new): New_
    {
        return $new;
    }

    private function sortNullableType(NullableType $nullableType): NullableType
    {
        return $nullableType;
    }

    private function sortPostDec(PostDec $postDec): PostDec
    {
        return $postDec;
    }

    private function sortPostInc(PostInc $postInc): PostInc
    {
        return $postInc;
    }

    private function sortPropertyProperty(PropertyProperty $propertyProperty): PropertyProperty
    {
        return $propertyProperty;
    }

    private function sortStaticCall(StaticCall $staticCall): StaticCall
    {
        return $staticCall;
    }

    private function sortThrow(Throw_ $throw): Throw_
    {
        return $throw;
    }

    private function sortUnaryMinus(UnaryMinus $unaryMinus): UnaryMinus
    {
        return $unaryMinus;
    }

    private function sortUnset(Unset_ $unset): Unset_
    {
        return $unset;
    }

    private function sortVariable(Variable $variable): Variable
    {
        return $variable;
    }

    private function sortWhile(While_ $while): While_
    {
        return $while;
    }
}
