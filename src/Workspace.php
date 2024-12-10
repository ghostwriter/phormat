<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use Ghostwriter\Filesystem\Interface\FilesystemInterface;
use Ghostwriter\Phormat\Exception\FailedToReadFileException;
use Ghostwriter\Phormat\Exception\FailedToWriteFileException;
use Ghostwriter\Phormat\Exception\FileNotFoundException;
use Ghostwriter\Phormat\Exception\MissingConfigFileException;
use Ghostwriter\Phormat\Exception\WorkspacePathDoesNotExistException;
use Ghostwriter\Phormat\Exception\WorkspacePathMustBeDirectoryException;
use Ghostwriter\Phormat\Exception\WorkspacePathMustBeNonEmptyStringException;

use const DIRECTORY_SEPARATOR;

final readonly class Workspace
{
    /**
     * @param non-empty-string $directory
     */
    public function __construct(
        private string $directory,
        private bool $dryRun = false
    ) {
    }

    /**
     * @throws WorkspacePathDoesNotExistException
     * @throws WorkspacePathMustBeDirectoryException
     * @throws WorkspacePathMustBeNonEmptyStringException
     */
    public static function new(string $directory): self
    {
        if (\trim($directory) === '') {
            throw new WorkspacePathMustBeNonEmptyStringException();
        }

        /** @var false|non-empty-string $realpath */
        $realpath = \realpath($directory);

        if ($realpath === false) {
            throw new WorkspacePathDoesNotExistException($directory);
        }

        if (! \is_dir($realpath)) {
            throw new WorkspacePathMustBeDirectoryException($directory);
        }

        return new self($realpath);
    }

    /**
     * @throws FailedToReadFileException
     * @throws FailedToWriteFileException
     * @throws FileNotFoundException
     * @throws MissingConfigFileException
     */
    public function config(FilesystemInterface $filesystem): PhormatConfig
    {
        $configPath = $this->path('phormat.php');

        if ($filesystem->missing($configPath)) {

            $configPath = $this->path('phormat.dist.php');

            if ($filesystem->missing($configPath)) {

                $filesystem->write(
                    $configPath,
                    $filesystem->read(\dirname(__DIR__) . DIRECTORY_SEPARATOR . 'phormat.dist.php')
                );
            }
        }

        if (! \is_file($configPath)) {
            throw new MissingConfigFileException($configPath);
        }

        /** @var PhormatConfig */
        return require $configPath;
    }

    public function dryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     * @return non-empty-string
     */
    public function path(string ...$names): string
    {
        /**
         * @var non-empty-string
         */
        return $this->directory . DIRECTORY_SEPARATOR . \implode(DIRECTORY_SEPARATOR, $names);
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->directory;
    }
}
