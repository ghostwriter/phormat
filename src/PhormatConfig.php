<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use Ghostwriter\Container\Container;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Phormat\Exception\NodeVisitorMustImplementNodeVisitorInterfaceException;
use Ghostwriter\Phormat\Exception\PathDoesNotExistException;
use Ghostwriter\Phormat\Exception\SkippedClassDoesNotExistException;
use Ghostwriter\Phormat\Exception\SkippedClassMustImplementNodeVisitorInterfaceException;
use Ghostwriter\Phormat\Exception\SkippedPathDoesNotExistException;
use Ghostwriter\Phormat\Exception\SkippedPathMustBeStringException;
use Ghostwriter\Phormat\Value\PhpFile;
use Ghostwriter\Phormat\Value\PhpFileFinder;
use Ghostwriter\Phormat\Value\PhpFiles;
use PhpParser\NodeVisitor;
use Throwable;

final class PhormatConfig
{
    /**
     * @param array<non-empty-string,PhpFile>                                         $paths
     * @param array<non-empty-string,bool>                                            $skip
     * @param array<class-string<NodeVisitor>,non-empty-array<non-empty-string,bool>> $skipNodeVisitor
     * @param array<class-string<NodeVisitor>>                              $nodeVisitors
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly PhpFileFinder $phpFileFinder,
        private readonly PhpFiles $phpFiles,
        private readonly array $nodeVisitors = [],
        private array $paths = [],
        private array $skip = [],
        private array $skipNodeVisitor = [],
    ) {
    }

    //    /** @var array<string,string> */
    //    private array $skip = [
    //        'path/to/directory',
    //        'path/to/file.php'
    //    ];
    //
    //    /** @var array<class-string<NodeVisitor>,bool> */
    //    private array $skipNodeVisitor = [
    //        'NodeVisitor' => [
    //            'path/to/directory',
    //            'path/to/file.php'
    //        ],
    //    ];
    /**
     * @param array<array-key,class-string<NodeVisitor>> $nodeVisitors
     *
     * @throws NodeVisitorMustImplementNodeVisitorInterfaceException
     * @throws Throwable
     */
    public static function new(string ...$nodeVisitors): self
    {
        $container = Container::getInstance();
        foreach ($nodeVisitors as $nodeVisitor) {
            if (! \is_a($nodeVisitor, NodeVisitor::class, true)) {
                throw new NodeVisitorMustImplementNodeVisitorInterfaceException(\sprintf(
                    'NodeVisitor "%s" MUST implement %s',
                    $nodeVisitor,
                    NodeVisitor::class
                ));
            }

            $container->register($nodeVisitor, $nodeVisitor, [NodeVisitor::class]);
        }

        return new self($container, $container->get(PhpFileFinder::class), PhpFiles::new(), $nodeVisitors);
    }

    /**
     * @return array<string,PhpFile>
     */
    public function filesAndDirectories(): array
    {
        $files = [];
        foreach ($this->paths as $path => $phpFile) {
            if (\is_dir($path)) {
                foreach ($this->phpFileFinder->find($path) as $file) {
                    $files[$file->path()] = $file;
                }

                continue;
            }

            $files[$path] = $phpFile;
        }

        foreach (\array_keys($this->skip) as $path) {
            if (\is_dir($path)) {
                foreach ($this->phpFileFinder->find($path) as $file) {
                    unset($files[$file->path()]);
                }

                continue;
            }

            unset($files[$path]);
        }

        return $files;
    }

    /**
     * @return non-empty-array<class-string<NodeVisitor>>
     */
    public function nodeVisitors(): array
    {
        return $this->nodeVisitors;
    }

    /**
     * @param non-empty-string $paths
     */
    public function paths(string ...$paths): self
    {
        foreach ($paths as $path) {
            if (\is_file($path) && \str_ends_with($path, '.php')) {
                $this->paths[$path] = PhpFile::new($path);
                continue;
            }

            if (! \is_dir($path)) {
                /** @var array{file:string,line:int} $debug */
                $debug = \debug_backtrace(0, 1)[0];
                $file = $debug['file'] . ':' . $debug['line'];
                throw new PathDoesNotExistException(\sprintf(
                    'Path "%s" does not exist; called from "/%s"',
                    $path,
                    $file
                ));
            }

            foreach ($this->phpFileFinder->find($path) as $file) {
                $this->paths[$file->path()] = $file;
            }
        }

        return new self(
            $this->container,
            $this->phpFileFinder,
            $this->phpFiles,
            $this->paths,
            $this->skip,
            $this->skipNodeVisitor,
            $this->nodeVisitors
        );
    }

    /**
     * @param non-empty-string $paths
     */
    public function skip(string ...$paths): self
    {
        foreach ($paths as $path) {
            $isPath = \is_dir($path) || \is_file($path);
            if (! $isPath) {
                throw new SkippedPathDoesNotExistException($path);
            }

            $this->skip[$path] = true;
        }

        return $this;
    }

    /**
     * @template T of NodeVisitor
     *
     * @param non-empty-array<class-string<T>,non-empty-array<non-empty-string>> $visitors
     *
     * @throws SkippedClassDoesNotExistException
     * @throws SkippedClassMustImplementNodeVisitorInterfaceException
     * @throws SkippedPathDoesNotExistException
     */
    public function skipVisitors(array $visitors): self
    {
        foreach ($visitors as $visitor => $paths) {
            if (! \class_exists($visitor)) {
                throw new SkippedClassDoesNotExistException($visitor);
            }

            if (! \is_a($visitor, NodeVisitor::class, true)) {
                throw new SkippedClassMustImplementNodeVisitorInterfaceException(\sprintf(
                    'Class "%s" MUST implement %s',
                    $visitor,
                    NodeVisitor::class
                ));
            }

            /**
             * @var non-empty-array<null|non-empty-string>|non-empty-string $paths
             */
            foreach ((array) $paths as $path) {
                if (! \is_string($path)) {
                    throw new SkippedPathMustBeStringException(\sprintf('Type "%s" given', \get_debug_type($path)));
                }

                if (! \str_contains($path, '*') && ! \file_exists($path)) {
                    throw new SkippedPathDoesNotExistException(\sprintf('Path "%s" does not exist', $path));
                }

                $this->skipNodeVisitor[$visitor][$path] = true;
            }
        }

        return $this;
    }

    /**
     * @return array<class-string<NodeVisitor>,non-empty-array<non-empty-string,bool>>
     */
    public function skippedNodeVisitors(): array
    {
        /** @var array<class-string<NodeVisitor>,non-empty-array<non-empty-string,bool>> $skipNodeVisitor */
        $skipNodeVisitor = [];
        foreach ($this->skipNodeVisitor as $nodeVisitor => $paths) {
            foreach (\array_keys($paths) as $path) {
                if (\is_file($path)) {
                    $skipNodeVisitor[$nodeVisitor][$path] = true;
                    continue;
                }

                if (\is_dir($path)) {
                    foreach ($this->phpFileFinder->find($path) as $file) {
                        $skipNodeVisitor[$nodeVisitor][$file->path()] = true;
                    }
                }
            }
        }

        return $skipNodeVisitor;
    }

    /**
     * @param class-string<NodeVisitor> $nodeVisitors
     *
     * @return $this
     */
    public function visitors(string ...$nodeVisitors): self
    {
        foreach ($nodeVisitors as $nodeVisitor) {
            if (! \is_a($nodeVisitor, NodeVisitor::class, true)) {
                throw new NodeVisitorMustImplementNodeVisitorInterfaceException(\sprintf(
                    'NodeVisitor "%s" MUST implement %s',
                    $nodeVisitor,
                    NodeVisitor::class
                ));
            }

            $this->container->tag($nodeVisitor, [NodeVisitor::class]);
        }

        return self::new(...$nodeVisitors);
    }

    /**
     * @param class-string<NodeVisitor>              $nodeVisitor
     * @param non-empty-array<null|non-empty-string> $paths
     *
     * @throws SkippedClassDoesNotExistException
     * @throws SkippedClassMustImplementNodeVisitorInterfaceException
     * @throws SkippedPathDoesNotExistException
     */
    private function skipNodeVisitor(string $nodeVisitor, array $paths): void
    {
        foreach ($this->nodeVisitors as $nodeVisitor) {
            if (! \class_exists($nodeVisitor)) {
                throw new SkippedClassDoesNotExistException($nodeVisitor);
            }

            if (! \is_a($nodeVisitor, NodeVisitor::class, true)) {
                throw new SkippedClassMustImplementNodeVisitorInterfaceException(\sprintf(
                    'Class "%s" MUST implement %s',
                    $nodeVisitor,
                    NodeVisitor::class
                ));
            }

            foreach ($paths as $path) {
                if (! \is_string($path)) {
                    throw new SkippedPathMustBeStringException(\sprintf('Type "%s" given', \get_debug_type($path)));
                }

                if (! \str_contains($path, '*') && ! \file_exists($path)) {
                    throw new SkippedPathDoesNotExistException(\sprintf('Path "%s" does not exist', $path));
                }

                // $this->skipNodeVisitor[$visitor][$path] = true;
            }
        }
    }
}
