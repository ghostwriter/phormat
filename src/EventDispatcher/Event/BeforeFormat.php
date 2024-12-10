<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\EventDispatcher\Event;

final readonly class BeforeFormat
{
    public function __construct(
        private string $file,
        private string $contents
    ) {
    }

    public function contents(): string
    {
        return $this->contents;
    }

    public function file(): string
    {
        return $this->file;
    }
}
