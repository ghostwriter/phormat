<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;

final readonly class NodeTraverserFactory implements FactoryInterface
{
    #[Override]
    public function __invoke(ContainerInterface $container, array $arguments = []): NodeTraverser
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new class() extends NodeVisitorAbstract {
            public function enterNode(Node $node): Node
            {
                $node->setAttribute(self::class, \spl_object_hash($node));
                return $node;
            }
        });
        $nodeTraverser->addVisitor(new class() extends NodeVisitorAbstract {
            public function enterNode(Node $node): Node
            {
                $node->setAttribute('origNode', clone $node);
                return $node;
            }
        });
        $nodeTraverser->addVisitor(new NameResolver(null, [
            'preserveOriginalNames' => true,
            'replaceNodes' => false,
        ]));
        return $nodeTraverser;
    }
}
