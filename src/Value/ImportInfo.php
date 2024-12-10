<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Value;

use PhpParser\Node\Stmt\Use_;

final class ImportInfo
{
    public function __construct(
        public string $fullName,
        public string $shortName,
        public string $alias,
        public int $type = Use_::TYPE_UNKNOWN,
        public bool $useAlias = false
    ) {
    }
}
