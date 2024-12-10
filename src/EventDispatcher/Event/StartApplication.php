<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\EventDispatcher\Event;

use Ghostwriter\Phormat\Workspace;

final readonly class StartApplication
{
    public function __construct(
        private string $name,
        private string $description,
        private string $version,
        private Workspace $workspace,
        private bool $dryRun = false
    ) {
    }

    public static function new(
        string $name,
        string $description,
        string $version,
        Workspace $workspace,
        bool $dryRun
    ): self {
        return new self($name, $description, $version, $workspace, $dryRun);
    }

    public function description(): string
    {
        return $this->description;
    }

    public function dryRun(): bool
    {
        return $this->dryRun;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function workspace(): Workspace
    {
        return $this->workspace;
    }
}
