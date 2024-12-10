<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use Closure;
use Generator;
use Ghostwriter\Filesystem\Interface\FilesystemInterface;
use SplFileInfo;

final readonly class PhpFileFinder
{
    private Closure $isPhpFile;

    public function __construct(
        private FilesystemInterface $filesystem
    ) {
        $this->isPhpFile = static fn (SplFileInfo $file): bool => $file->getExtension() === 'php';
    }

    /**
     * @param null|Closure(SplFileInfo):bool $match
     * @param null|Closure(SplFileInfo):bool $skip
     *
     * @return Generator<string>
     */
    public function find(string $path, ?Closure $match = null, ?Closure $skip = null): Generator
    {
        $match ??= $this->isPhpFile;
        $skip ??= static fn (SplFileInfo $file): bool => false;
        foreach ($this->filesystem->recursiveDirectoryIterator($path) as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }

            if (false === ($this->isPhpFile)($file)) {
                continue;
            }

            if ($match($file) === false) {
                continue;
            }
            if ($skip($file) === true) {
                continue;
            }

            (yield $file->getPathname());
        }
    }
}
