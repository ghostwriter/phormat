<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Value;

use Ghostwriter\Phormat\Exception\FailedToReadFileException;
use Ghostwriter\Phormat\Exception\FileDoesNotExistException;
use Ghostwriter\Phormat\Exception\FileIsEmptyException;

final readonly class PhpFile
{
    /**
     * @param non-empty-string $path
     * @param non-empty-string $contents
     *
     * @throws FileDoesNotExistException
     * @throws FileIsEmptyException
     */
    public function __construct(
        private string $path,
        private string $contents
    ) {
        if (! \file_exists($path)) {
            throw new FileDoesNotExistException($path);
        }

        if (\trim($contents) === '') {
            throw new FileIsEmptyException($path);
        }
    }

    /**
     * @param non-empty-string $path
     *
     * @throws FailedToReadFileException
     */
    public static function new(string $path): self
    {
        /** @var false|non-empty-string $contents */
        $contents = \file_get_contents($path);
        if ($contents === false) {
            throw new FailedToReadFileException($path);
        }

        return new self($path, $contents);
    }

    /**
     * @return non-empty-string
     */
    public function contents(): string
    {
        return $this->contents;
    }

    /**
     * @return non-empty-string
     */
    public function path(): string
    {
        return $this->path;
    }
}
