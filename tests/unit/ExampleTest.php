<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Phormat\Example;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(Example::class)]
final class ExampleTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testExample(): void
    {
        self::assertTrue(Example::new()->test());
    }
}
