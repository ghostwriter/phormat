<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Override;
use PhpParser\PhpVersion;
use PhpParser\PrettyPrinter\Standard;
use Throwable;

/**
 * @implements FactoryInterface<Standard>
 */
final readonly class StandardFactory implements FactoryInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): Standard
    {
        return new Standard([
            'phpVersion' => PhpVersion::fromComponents(8, 3),
            'shortArraySyntax' => true,
            'newline' => "\n",
        ]);
    }
}
