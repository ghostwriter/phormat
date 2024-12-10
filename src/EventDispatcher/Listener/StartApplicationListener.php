<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\EventDispatcher\Listener;

use Ghostwriter\Phormat\EventDispatcher\Event\StartApplication;
use Symfony\Component\Console\Style\SymfonyStyle;

use const PHP_VERSION;

final readonly class StartApplicationListener
{
    public function __construct(
        private SymfonyStyle $symfonyStyle
    ) {
    }

    public function __invoke(StartApplication $startApplication): void
    {
        $this->symfonyStyle->title(
            \sprintf(
                '%s <info>(%s)</info> - %s %s',
                $startApplication->name(),
                $startApplication->version(),
                $startApplication->description(),
                '<error>#BlackLivesMatter</error>'
            )
        );
        $this->symfonyStyle->writeln('PHP version: ' . PHP_VERSION);
        $this->symfonyStyle->writeln('Current working directory: ' . $startApplication->workspace()->toString());
    }
}
