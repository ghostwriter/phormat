<?php

declare(strict_types=1);

namespace Vendor\Package;

final class Foo extends Bar implements FooInterface
{
    public function sampleFunction(int $a, ?int $b = null): array
    {
        if ($a === $b) {
            \bar();
        } elseif ($a > $b) {
            $foo->bar($arg1);
        } else {
            BazClass::bar($arg2, $arg3);
        }
    }

    public static function bar(): void
    {
        // method body
    }
}

enum DoorState: string
{
    case CLOSED = 'closed';
    case LOCKED = 'locked';
    case OPEN = 'open';

    public function isClosed(): bool
    {
        return $this->value === self::CLOSED;
    }

    public function isLocked(): bool
    {
        return $this->value === self::LOCKED;
    }

    public function isOpen(): bool
    {
        return $this->value === self::OPEN;
    }

    public function state(): string
    {
        return match ($this->value) {
            self::CLOSED => 'closed',
            self::LOCKED => 'locked',
            self::OPEN => 'open',
        };
    }
}

enum Foo: string
{
    case BAR = 'bar';
    case BAZ = 'baz';
}

interface BarInterface
{
    public function bar(): void;
}

interface FooInterface
{
    public function foo(): void;
}

abstract class ZooBar
{
}

trait One
{
}

trait Three
{
}

trait Two
{
}

enum Beep: int
{
    case Bar = 2;
    case Foo = 1;

    public function isOdd(): bool
    {
        return $this->value() % 2;
    }
}
