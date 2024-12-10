<?php

declare(strict_types=1);

/**
 * Js walk DOM Tree - to php
 *
 * function walk(node, func) {
 *   func(node);
 *   node = node.firstChild;
 *   while (node) {
 *     walk(node, func);
 *     node = node.nextSibling;
 *   }
 * };
 *
 * walk(document.body, function(node) {
 *   if (node.nodeType === Node.ELEMENT_NODE) {
 *     var tagName = node.tagName.toLowerCase();
 *     if (tagName === 'img') {
 *       node.src = 'http://placekitten.com/' + node.width + '/' + node.height;
 *     }
 *   }
 * });
 */
interface DocumentInterface
{
    public function body(): \NodeInterface;
}

interface NodeInterface
{
    public function firstChild(): ?self;

    public function nextSibling(): ?self;

    public function parent(): ?self;
}

final class Img implements \NodeInterface
{
    /**
     * @var array<\NodeInterface>
     */
    public array $children = [];

    public int $height = 0;

    public ?\NodeInterface $next = null;

    public ?\NodeInterface $parent = null;

    public ?\NodeInterface $previous = null;

    public string $src = '';

    public int $width = 0;

    #[\Override]
    public function firstChild(): ?\NodeInterface
    {
        foreach ($this->children as $child) {
            if (! $child instanceof \NodeInterface) {
                continue;
            }

            return $child;
        }

        return null;
    }

    #[\Override]
    public function nextSibling(): ?\NodeInterface
    {
        return $this->next;
    }

    #[\Override]
    public function parent(): ?\NodeInterface
    {
        return $this->parent;
    }
}

function walk(\NodeInterface $node, \Closure $call): void
{
    $call($node);

    $node = $node->firstChild();

    while ($node instanceof \NodeInterface) {
        \walk($node, $call);

        $node = $node->nextSibling();
    }
}

$document = new class() implements \DocumentInterface {
    #[\Override]
    public function body(): \NodeInterface
    {
        return new \Img();
    }
};

\walk($document->body(), static function (\NodeInterface $node): void {
    if ($node instanceof \Img) {
        $node->src = \sprintf('https://placekitten.com/%s/%s', $node->width + 25, $node->height + 25);
    }

    \var_dump($node);
});
