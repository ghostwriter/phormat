<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Value;

use Generator;
use Ghostwriter\Filesystem\Interface\FilesystemInterface;
use InvalidArgumentException;
use RegexIterator;
use SplFileInfo;

final readonly class PhpFileFinder
{
    public function __construct(
        private FilesystemInterface $filesystem
    ) {
    }

    /**
     * @param non-empty-string $directory
     *
     * @throws InvalidArgumentException
     *
     * @return Generator<PhpFile>
     */
    public function find(string $directory): Generator
    {
        if (! \is_dir($directory)) {
            throw new InvalidArgumentException(\sprintf('Directory "%s" does not exist', $directory));
        }

        foreach (new RegexIterator($this->filesystem->recursiveDirectoryIterator(
            $directory
        ), '/^.+\.php$/i', RegexIterator::MATCH) as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }

            yield PhpFile::new($file->getRealPath());
        }
    }
}
