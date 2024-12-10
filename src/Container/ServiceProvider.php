<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Container;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ServiceProviderInterface;
use Ghostwriter\EventDispatcher\EventDispatcher;
use Ghostwriter\EventDispatcher\Interface\EventDispatcherInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Ghostwriter\Filesystem\Filesystem;
use Ghostwriter\Filesystem\Interface\FilesystemInterface;
use Ghostwriter\Phormat\Container\Extension\ListenerProviderExtension;
use Ghostwriter\Phormat\Container\Factory\ListenerProviderFactory;
use Ghostwriter\Phormat\Container\Factory\NameResolverFactory;
use Ghostwriter\Phormat\Container\Factory\ParserFactory;
use Ghostwriter\Phormat\Container\Factory\StandardFactory;
use Ghostwriter\Phormat\NodeVisitor\IdentifierNodeVisitor;
use Override;
use PhpParser\ErrorHandler;
use PhpParser\ErrorHandler\Throwing;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use SebastianBergmann\Diff\Output\DiffOutputBuilderInterface;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final readonly class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $container->alias(ArgvInput::class, InputInterface::class);
        $container->alias(EventDispatcher::class, EventDispatcherInterface::class);
        $container->alias(ConsoleOutput::class, OutputInterface::class);
        $container->alias(Filesystem::class, FilesystemInterface::class);
        $container->alias(SymfonyStyle::class, StyleInterface::class);
        $container->alias(Throwing::class, ErrorHandler::class);
        $container->alias(UnifiedDiffOutputBuilder::class, DiffOutputBuilderInterface::class);

        $container->extend(ListenerProviderInterface::class, ListenerProviderExtension::class);

        $container->factory(ListenerProviderInterface::class, ListenerProviderFactory::class);
        $container->factory(Parser::class, ParserFactory::class);
        $container->factory(NameResolver::class, NameResolverFactory::class);
        $container->factory(Standard::class, StandardFactory::class);

        $container->tag(IdentifierNodeVisitor::class, [NodeVisitor::class]);
        $container->tag(NameResolver::class, [NodeVisitor::class]);
        $container->tag(CloningVisitor::class, [NodeVisitor::class]);
        $container->tag(ParentConnectingVisitor::class, [NodeVisitor::class]);
        $container->tag(ParentConnectingVisitor::class, [NodeVisitor::class]);

        //$container->tag(NodeConnectingVisitor::class, [NodeVisitor::class]);
    }
}
