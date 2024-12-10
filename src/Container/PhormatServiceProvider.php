<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Container;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Exception\NotFoundExceptionInterface;
use Ghostwriter\Container\Interface\ExceptionInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\Phormat\Container\Factory\NodeTraverserFactory;
use Ghostwriter\Phormat\Container\Factory\ParserFactory;
use Ghostwriter\Phormat\Formatter;
use Ghostwriter\Phormat\FormatterInterface;
use Override;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use SebastianBergmann\Diff\Output\DiffOutputBuilderInterface;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class PhormatServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ExceptionInterface
     */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $container->alias(FormatterInterface::class, Formatter::class);
        $container->alias(DiffOutputBuilderInterface::class, UnifiedDiffOutputBuilder::class);
        $container->alias(InputInterface::class, ArgvInput::class);
        $container->alias(OutputInterface::class, ConsoleOutput::class);
        $container->factory(Parser::class, ParserFactory::class);
        $container->factory(NodeTraverser::class, NodeTraverserFactory::class);
    }
}
