<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Container\Extension;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\ExtensionInterface;
use Ghostwriter\EventDispatcher\Interface\ListenerProviderInterface;
use Override;

/**
 * @implements ExtensionInterface<ListenerProviderInterface>
 */
final readonly class ListenerProviderExtension implements ExtensionInterface
{
    /**
     * @param ListenerProviderInterface $service
     *
     * @return ListenerProviderInterface
     */
    #[Override]
    public function __invoke(ContainerInterface $container, object $service): object
    {
        //        dump($container);
        //        dump($service::class, debug_backtrace(0,3));
        //        $service->bind(StartApplication::class, StartApplicationListener::class);
        //
        //        $service->bind(LocatePhormatConfig::class, LocatePhormatConfigListener::class);
        return $service;
    }
}
