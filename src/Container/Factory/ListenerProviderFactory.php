<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Container\Factory;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\FactoryInterface;
use Ghostwriter\EventDispatcher\ListenerProvider;
use Ghostwriter\Phormat\EventDispatcher\Event\LocatePhormatConfig;
use Ghostwriter\Phormat\EventDispatcher\Event\StartApplication;
use Ghostwriter\Phormat\EventDispatcher\Listener\LocatePhormatConfigListener;
use Ghostwriter\Phormat\EventDispatcher\Listener\StartApplicationListener;
use Override;
use Throwable;

/**
 * @implements FactoryInterface<ListenerProvider>
 */
final readonly class ListenerProviderFactory implements FactoryInterface
{
    /**
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container): ListenerProvider
    {
        $listenerProvider = new ListenerProvider($container);
        $listenerProvider->bind(StartApplication::class, StartApplicationListener::class);
        $listenerProvider->bind(LocatePhormatConfig::class, LocatePhormatConfigListener::class);
        //        $listenerProvider->bind(FormatPhpFiles::class, FormatPhpFilesListener::class);
        return $listenerProvider;
    }
}
