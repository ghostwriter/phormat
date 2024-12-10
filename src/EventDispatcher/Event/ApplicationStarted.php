<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\EventDispatcher\Event;

final readonly class ApplicationStarted
{
    public function __construct(
        private string $directory,
    ) {
    }

    public function directory(): string
    {
        return $this->directory;
    }
}
