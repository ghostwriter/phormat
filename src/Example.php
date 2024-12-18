<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

final class Example
{
    public static function new(): self
    {
        return new self();
    }

    public function test(): bool
    {
        return true;
    }
}
