<?php

declare(strict_types=1);

namespace Ghostwriter\PhormatTests\Unit;

use Ghostwriter\Phormat\ColorConsoleDiff;
use Ghostwriter\Phormat\Container\Extension\ListenerProviderExtension;
use Ghostwriter\Phormat\Container\Factory\ListenerProviderFactory;
use Ghostwriter\Phormat\Container\Factory\ParserFactory;
use Ghostwriter\Phormat\Container\Factory\StandardFactory;
use Ghostwriter\Phormat\Container\PhormatServiceProvider;
use Ghostwriter\Phormat\Container\ServiceProvider;
use Ghostwriter\Phormat\Phormat;
use Ghostwriter\Phormat\PhormatInterface;
use Ghostwriter\Phormat\PhpFileFinder;
use Ghostwriter\Phormat\Printer;
use Ghostwriter\Phormat\Runner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Phormat::class)]
#[CoversClass(ColorConsoleDiff::class)]
#[CoversClass(PhormatServiceProvider::class)]
#[CoversClass(PhpFileFinder::class)]
#[CoversClass(Printer::class)]
#[CoversClass(ListenerProviderExtension::class)]
#[CoversClass(ListenerProviderFactory::class)]
#[CoversClass(ParserFactory::class)]
#[CoversClass(StandardFactory::class)]
#[CoversClass(Runner::class)]
#[CoversClass(ServiceProvider::class)]
#[CoversClass(\Ghostwriter\Phormat\Value\PhpFileFinder::class)]
final class PhormatTest extends TestCase
{
    public function test(): void
    {
        self::assertInstanceOf(PhormatInterface::class, Phormat::new());
    }
}
