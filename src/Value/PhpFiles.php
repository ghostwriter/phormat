<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Value;

use Closure;
use Countable;
use Generator;
use IteratorAggregate;
use Override;
use RuntimeException;
use WeakMap;

/** @implements IteratorAggregate<PhpFile> */
final class PhpFiles implements Countable, IteratorAggregate
{
    /**
     * @param WeakMap<PhpFile, non-empty-string> $weakMap
     */
    public function __construct(
        private WeakMap $weakMap
    ) {
    }

    public static function new(PhpFile ...$phpFiles): self
    {
        /** @var WeakMap<PhpFile,non-empty-string> $newPhpFiles */
        $weakMap = new WeakMap();
        foreach ($phpFiles as $phpFile) {
            $weakMap[$phpFile] = $phpFile->path();
        }

        return new self($weakMap);
    }

    public function add(PhpFile $phpFile): self
    {
        $this->weakMap[$phpFile] = $phpFile->path();
        return $this;
    }

    #[Override]
    public function count(): int
    {
        return $this->weakMap->count();
    }

    /**
     * @param Closure(PhpFile):bool $callback
     */
    public function filter(Closure $callback): self
    {
        /** @var WeakMap<PhpFile,non-empty-string> $weakMap */
        $weakMap = new WeakMap();
        foreach ($this->weakMap as $phpFile => $path) {
            if ($callback($phpFile) !== true) {
                continue;
            }

            $weakMap[$phpFile] = $path;
        }

        return new self($weakMap);
    }

    #[Override]
    public function getIterator(): Generator
    {
        yield from $this->weakMap;
    }

    /**
     * @param Closure(PhpFile):PhpFile $callback
     */
    public function map(Closure $callback): self
    {
        /** @var WeakMap<PhpFile,non-empty-string> $weakMap */
        $weakMap = new WeakMap();

        foreach ($this->weakMap as $phpFile => $path) {
            $mappedPhpFile = $callback($phpFile);
            if (! $mappedPhpFile instanceof PhpFile) {
                throw new RuntimeException(\sprintf('Callback must return an instance of %s', PhpFile::class));
            }
            $weakMap[$mappedPhpFile] = $path;
        }

        return new self($weakMap);
    }

    /**
     * @return array<PhpFile,non-empty-string>
     */
    public function toArray(): array
    {
        return \iterator_to_array($this->weakMap);
    }
}
