<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;
use PhpParser\ErrorHandler;
use PhpParser\NodeVisitor\NameResolver;
use Throwable;

/**
 * @implements FactoryInterface<NameResolver>
 */
final readonly class NameResolverFactory implements FactoryInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): NameResolver
    {
        return new NameResolver($container->get(ErrorHandler::class));
    }
}
