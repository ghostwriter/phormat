<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Phormat\PhormatConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhormatConfig::class)]
final class PhormatConfigTest extends TestCase
{
    public function testExample(): void
    {
        self::assertTrue(true);
    }
}
