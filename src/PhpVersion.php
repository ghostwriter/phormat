<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

final readonly class PhpVersion
{
    public function __construct(
        private int $major,
        private int $minor
    ) {
    }

    public static function new(int $major, int $minor): self
    {
        return new self($major, $minor);
    }

    public static function new(int $major, int $minor): self
    {
        return new self($major, $minor);
    }

    public function atLeast(int $major, int $minor): bool
    {
        return $this->major >= $major && $this->minor >= $minor;
    }

    public function atMost(int $major, int $minor): bool
    {
        return $this->major <= $major && $this->minor <= $minor;
    }

    public function is(int $major, int $minor): bool
    {
        return $this->major === $major && $this->minor === $minor;
    }

    public function lessThan(int $major, int $minor): bool
    {
        return $this->major < $major || $this->minor < $minor;
    }

    public function major(): int
    {
        return $this->major;
    }

    public function minor(): int
    {
        return $this->minor;
    }

    public function moreThan(int $major, int $minor): bool
    {
        return $this->major > $major || $this->minor > $minor;
    }

    public function moreThan(int $major, int $minor): bool
    {
        return $this->major > $major || $this->minor > $minor;
    }

    public function version(): string
    {
        return $this->major . '.' . $this->minor;
    }
}
