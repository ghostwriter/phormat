<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\NodeVisitor;

use Ghostwriter\Phormat\Value\ImportInfo;
use Ghostwriter\Phormat\Value\UseStatements;
use Override;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\UseItem;
use PhpParser\NodeVisitorAbstract;

use function array_key_exists;
use function trim;

final class ImportFullyQualifiedNamesNodeVisitor extends NodeVisitorAbstract
{
    private array $aliases = [];

    private array $import = [];

    /** @var array<string, ImportInfo> */
    private array $importMap = [];

    public function __construct(
        private readonly UseStatements $useStatements = new UseStatements()
    ) {
    }

    #[Override]
    public function afterTraverse(array $nodes): ?array
    {
        $useStatements = [];
        foreach ($this->importMap as $fullName => $importInfo) {
            $useAlias = $importInfo->useAlias;
            $shortName = $useAlias ? $importInfo->alias : $importInfo->shortName;
            $alias = $useAlias ? $importInfo->alias : null;
            $type = $importInfo->type;
            $useStatements[$fullName] = new Use_([new UseItem(new Name($fullName), $alias, $type)]);
        }

        //        dump($useStatements, $this->importMap, $this->importMap);
        foreach ($nodes as $node) {
            if (! $node instanceof Namespace_) {
                continue;
            }

            // Insert use statements at the beginning of the file
            $node->stmts = \array_merge(
                $useStatements,
                \array_filter($node->stmts, static fn ($stmt): bool => ! $stmt instanceof Use_)
            );
            break;
        }

        return $nodes;
    }

    #[Override]
    public function beforeTraverse(array $nodes): ?array
    {
        $this->reset();
        foreach ($nodes as $node) {
            if (! $node instanceof Namespace_) {
                continue;
            }

            foreach ($node->stmts as $stmt) {
                if (! $stmt instanceof Use_) {
                    continue;
                }

                foreach ($stmt->uses as $use) {
                    $name = $use->name;
                    $fullName = $name->toString();
                    $shortName = $name->getLast();
                    //                    if (! str_contains($fullName, '\\')) {
                    //                        $this->import($fullName, $shortName, $alias
                    //
                    //                        continue;
                    //                    }
                    $alias = \trim($name->getFirst() . $shortName, '_');
                    //                    $type = $this->useType($fullName);
                    //                    if ($type !== Use_::TYPE_NORMAL) {
                    //                        $this->import($fullName, $shortName, $alias);
                    //                        continue;
                    //                    }
                    //                    if (! array_key_exists($shortName, $this->importMap[$type])) {
                    //                        $this->import($fullName, $shortName, $alias);
                    //
                    //                        continue;
                    //                    }
                    //                    if ($this->importMap[$type][$shortName] === $fullName) {
                    //                        continue;
                    //                    }
                    //                    $alias = trim($name->getFirst(). $name->getLast(),'_');
                    $this->import($fullName, $shortName, $alias, $use->alias !== null);
                }

                $use->alias = new Identifier($this->aliases[$type]);
            }
        }

        return null;
    }

    #[Override]
    public function enterNode(Node $node): ?Node
    {
        //        if (! $node instanceof FullyQualified) {
        //            return null;
        //        }
        if (! $node instanceof Name) {
            return null;
        }

        $parentNode = $node->getAttribute('parent');
        if ($parentNode instanceof Namespace_ || $parentNode instanceof UseUse) {
            return $node;
        }

        //        if (! $parentNode instanceof Param && ! $parentNode instanceof ClassMethod) {
        //            return $node;
        //        }
        $nodeFullName = $node->toString();
        $type = $this->useType($nodeFullName);
        if (\array_key_exists($nodeFullName, $this->importMap)) {
            $importInfo = $this->importMap[$nodeFullName];
            if ($importInfo->useAlias) {
                return new Name($importInfo->alias);
            }

            return new Name($importInfo->shortName);
        }

        return new Name($node->getLast());
    }

    public function import(string $fullName, string $shortName, string $alias, bool $useAlias): void
    {
        $this->importMap[$fullName] = new ImportInfo($fullName, $shortName, $alias, $this->useType(
            $fullName
        ), $useAlias);
    }

    public function imported(string $fullName, string $shortName, string $alias): bool
    {
        return \array_key_exists(
            $fullName,
            $this->importMap
        ) && $this->importMap[$fullName]->alias === $alias && $this->importMap[$fullName]->shortName === $shortName;
        $this->importMap[$fullName] = (object) [
            'alias' => $alias,
            'fullName' => $fullName,
            'shortName' => $shortName,
            'type' => $this->useType($fullName),
        ];
    }

    #[Override]
    public function leaveNode(Node $node): ?Node
    {
        return match (true) {
            $node instanceof Namespace_ => $this->leaveNamespace($node),
            default => null,
        };
    }

    public function reset(): void
    {
        $this->importMap = [];
    }

    public function useType(string $nodeFullName): int
    {
        return match (true) {
            \function_exists($nodeFullName) => Use_::TYPE_FUNCTION,
            \defined($nodeFullName) => Use_::TYPE_CONSTANT,
            default => Use_::TYPE_NORMAL,
        };
    }

    private function enterName(Name $node): Name
    {
        if ($node->getAttribute('parent') instanceof Param) {
            $type = $node->toString();
            if (! \str_contains($type, '\\')) {
                return $node;
            }

            if (! $this->useStatements->has($type)) {
                return $node;
            }

            $useStatement = $this->useStatements->get($type);
            $name = $useStatement->name();
            if ($this->useStatements->has($name)) {
                $name = $useStatement->alias();
                $this->aliases[$type] = $name;
            }

            return new Name($name);
        }

        return $node;

        return match (true) {
            $node instanceof Name, true === 1 => $this->enterName($node),
            $node instanceof Namespace_ => $this->enterNamespace($node),
            default => null,
        };
    }

    private function enterNamespace(Namespace_ $namespace): Namespace_
    {
        foreach ($namespace->stmts as $stmt) {
            if (! $stmt instanceof Use_) {
                continue;
            }

            foreach ($stmt->uses as $use) {
                $this->useStatements->set($use->name->toString());
            }
        }

        return $namespace;
    }
}
