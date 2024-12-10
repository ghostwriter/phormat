<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\EventDispatcher\Event;

use Ghostwriter\Phormat\Workspace;

final readonly class LocatePhormatConfig
{
    public function __construct(
        private Workspace $workspace
    ) {
    }

    public static function new(Workspace $workspace): self
    {
        return new self($workspace);
    }

    public function workspace(): Workspace
    {
        return $this->workspace;
    }
}
