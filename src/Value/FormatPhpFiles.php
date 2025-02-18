<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Value;

final readonly class FormatPhpFiles
{
    /**
     * @param list<PhpFile> $phpFiles
     */
    public function __construct(
        private array $phpFiles
    ) {}

    public static function new(PhpFile ...$phpFile): self
    {
        return new self($phpFile);
    }

    /**
     * @return list<PhpFile>
     */
    public function phpFiles(): array
    {
        return $this->phpFiles;
    }
}
