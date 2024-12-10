<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Phormat\NodeVisitor\Core\FileNodeVisitor;
use Ghostwriter\Phormat\NodeVisitor\Core\HashNodeVisitor;
use Ghostwriter\Phormat\Value\PhpFile;
use Ghostwriter\Phormat\Value\PhpFileFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use RuntimeException;
use SebastianBergmann\Diff\Differ as SebastianBergmannDiffer;
use Throwable;

final readonly class Runner
{
    public function __construct(
        private ContainerInterface $container,
        private SebastianBergmannDiffer $sebastianBergmannDiffer,
        private Parser $parser,
        private PhpFileFinder $phpFileFinder,
        private Standard $standard
    ) {
    }

    /**
     * @throws Throwable
     */
    public function run(PhormatConfig $phormatConfig, PhpFile $phpFile): Result
    {
        $skippedNodeVisitors = $phormatConfig->skippedNodeVisitors();
        $nodeTraverser = new NodeTraverser();
        $path = $phpFile->path();

        $nodeTraverser->addVisitor(new FileNodeVisitor($path));
        $nodeTraverser->addVisitor(new HashNodeVisitor());
        $nodeTraverser->addVisitor(new NodeVisitor\NameResolver());
        foreach ($this->container->tagged(NodeVisitor::class) as $nodeVisitor) {
            if (! $nodeVisitor instanceof NodeVisitor) {
                continue;
            }

            if (\array_key_exists(key: $nodeVisitor::class, array: $skippedNodeVisitors) && \array_key_exists(
                key: $path,
                array: $skippedNodeVisitors[$nodeVisitor::class]
            )) {
                continue;
            }

            $nodeTraverser->addVisitor($nodeVisitor);
        }

        $origStmts = $this->parser->parse($phpFile->contents());
        if ($origStmts === null) {
            throw new RuntimeException('Failed to parse PHP code');
        }

        return Result::new(
            sebastianBergmannDiffer: $this->sebastianBergmannDiffer,
            phormatConfig: $phormatConfig,
            phpFile: $phpFile,
            content: $this->standard->printFormatPreserving(stmts: $nodeTraverser->traverse(
                $origStmts
            ), origStmts: $origStmts, origTokens: $this->parser->getTokens())
        );
    }

    private function b(string $path): void
    {
    }

    private function d(string $path): void
    {
    }
}
