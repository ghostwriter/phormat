<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;
use PhpParser\Parser;
use Throwable;

/**
 * @implements FactoryInterface<Parser>
 */
final readonly class ParserFactory implements FactoryInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): Parser
    {
        return $container->get(\PhpParser\ParserFactory::class)->createForHostVersion();
    }
}
