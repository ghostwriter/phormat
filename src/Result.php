<?php

declare(strict_types=1);

namespace Ghostwriter\Phormat;

use Ghostwriter\Phormat\Value\PhpFile;
use SebastianBergmann\Diff\Differ as SebastianBergmannDiffer;

final readonly class Result
{
    public function __construct(
        private string $content,
        private PhormatConfig $phormatConfig,
        private PhpFile $phpFile,
        private SebastianBergmannDiffer $sebastianBergmannDiffer,
    ) {
    }

    public static function new(
        SebastianBergmannDiffer $sebastianBergmannDiffer,
        PhormatConfig $phormatConfig,
        PhpFile $phpFile,
        string $content
    ): self {
        return new self(
            content: $content,
            phormatConfig: $phormatConfig,
            phpFile: $phpFile,
            sebastianBergmannDiffer: $sebastianBergmannDiffer
        );
    }

    public function config(): PhormatConfig
    {
        return $this->phormatConfig;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function diff(): string
    {
        return $this->sebastianBergmannDiffer->diff(from: $this->phpFile->contents(), to: $this->content);
    }

    public function hasNotChanged(): bool
    {
        return $this->content === $this->phpFile->contents();
    }

    public function originalContent(): string
    {
        return $this->phpFile->contents();
    }

    public function phpFile(): PhpFile
    {
        return $this->phpFile;
    }

    public function updated(): bool
    {
        return $this->content !== $this->phpFile->contents();
    }

    public function updatedContent(): string
    {
        return $this->content;
    }
}
