<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Value;

final readonly class UseStatement
{
    private string $alias;

    private string $name;

    public function __construct(
        private string $full
    ) {
        $parts = \explode('\\', $full);
        $first = $parts[\array_key_first($parts)];
        $last = $parts[\array_key_last($parts)];
        $this->alias = $first === $last ? $last : $first . $last;
        $this->name = $last;
    }

    public function alias(): string
    {
        return $this->alias;
    }

    public function full(): string
    {
        return $this->full;
    }

    public function name(): string
    {
        return $this->name;
    }
}
