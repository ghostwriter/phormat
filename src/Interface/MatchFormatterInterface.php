<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat\Interface;

use PhpParser\Node;

interface FormatterInterface
{
    public function format(Node $node): null|array|int|Node;
}
interface MatchFormatterInterface extends FormatterInterface
{
    public function match(Node $node): bool;
}
interface ComposerPackageVersionFormatterInterface extends FormatterInterface
{
    /**
     * Package name => version constraint.
     *
     * @return array<string,string>
     */
    public function packages(): array;
}
interface ComposerVersionFormatterInterface extends FormatterInterface
{
    public function composerVersion(Node $node): string;
}
interface ComposerPackageFormatterInterface extends FormatterInterface
{
    public function composerVersion(Node $node): string;
}
interface SkipFormatterInterface extends FormatterInterface
{
    public function skip(Node $node): bool;
}
