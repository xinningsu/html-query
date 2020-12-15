<?php

namespace Sulao\HtmlQuery;

use DOMNode;
use DOMNodeList;

/**
 * Class HtmlQueryNode
 *
 * @package Sulao\HtmlQuery
 */
abstract class HtmlQueryNode extends HtmlQueryAttribute
{
    /**
     * Insert content or node(s) before each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function before($content)
    {
        $content = $this->contentResolve($content);

        return $this->each(function (HtmlNode $node, $index) use ($content) {
            $content->each(function (DOMNode $newNode) use ($node, $index) {
                $newNode = $this->newNode($newNode, $index);
                $node->before($newNode);
            });
        });
    }

    /**
     * Insert every matched node before the target.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function insertBefore($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->before($this);
    }

    /**
     * Insert content or node(s) after each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function after($content)
    {
        $content = $this->contentResolve($content);

        return $this->each(function (HtmlNode $node, $index) use ($content) {
            $content->each(function (DOMNode $newNode) use ($node, $index) {
                $newNode = $this->newNode($newNode, $index);
                $node->after($newNode);
            }, true);
        });
    }

    /**
     * Insert every matched node after the target.
     *
     * @param string|DOMNode|DOMNode[]DOMNodeList|static $selector
     *
     * @return static
     */
    public function insertAfter($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->after($this);
    }

    /**
     * Insert content or node(s) to the end of every matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function append($content)
    {
        $content = $this->contentResolve($content);

        return $this->each(function (HtmlNode $node, $index) use ($content) {
            $content->each(function (DOMNode $newNode) use ($node, $index) {
                $newNode = $this->newNode($newNode, $index);
                $node->append($newNode);
            });
        });
    }

    /**
     * Insert every matched node to the end of the target.
     *
     * @param string|DOMNode|DOMNode[]DOMNodeList|static $selector
     *
     * @return static
     */
    public function appendTo($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->append($this);
    }

    /**
     * Insert content or node(s) to the beginning of each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function prepend($content)
    {
        $content = $this->contentResolve($content);

        return $this->each(function (HtmlNode $node, $index) use ($content) {
            $content->each(function (DOMNode $newNode) use ($node, $index) {
                $newNode = $this->newNode($newNode, $index);
                $node->prepend($newNode);
            }, true);
        });
    }

    /**
     * Insert every matched node to the beginning of the target.
     *
     * @param string|DOMNode|DOMNode[]DOMNodeList|static $selector
     *
     * @return static
     */
    public function prependTo($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->prepend($this);
    }

    /**
     * Replace each matched node with the provided new content or node(s)
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function replaceWith($content)
    {
        $content = $this->contentResolve($content);
        return $this->each(function (DOMNode $node, $index) use ($content) {
            if (!$node->parentNode) {
                return;
            }

            $len = $content->count();
            $content->each(
                function (DOMNode $newNode) use ($node, $index, $len) {
                    $newNode = $this->newNode($newNode, $index);

                    if ($len === 1) {
                        $node->parentNode->replaceChild($newNode, $node);
                    } else {
                        $this->resolve($newNode)->insertAfter($node);
                    }
                },
                true
            );

            if ($len !== 1) {
                $node->parentNode->removeChild($node);
            }
        });
    }

    /**
     * Replace each target node with the matched node(s)
     *
     * @param string|DOMNode|DOMNode[]DOMNodeList|static $selector
     *
     * @return static
     */
    public function replaceAll($selector)
    {
        $target = $this->targetResolve($selector);

        return $target->replaceWith($this);
    }

    /**
     * Wrap an HTML structure around each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function wrap($content)
    {
        $content = $this->contentResolve($content);
        $newNode = $content[0];

        if (empty($newNode)) {
            return $this;
        }

        return $this->each(function (DOMNode $node, $index) use ($newNode) {
            $newNode = $this->newNode($newNode, $index);

            $nodes = $this->xpathQuery('descendant::*[last()]', $newNode);
            if (!$nodes) {
                throw new Exception('Invalid wrap html format.');
            }

            $deepestNode = end($nodes);
            $node->parentNode->replaceChild($newNode, $node);
            $deepestNode->appendChild($node);
        });
    }

    /**
     * Wrap an HTML structure around the content of each matched node.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function wrapInner($content)
    {
        $content = $this->contentResolve($content);
        $newNode = $content[0];

        if (empty($newNode)) {
            return $this;
        }

        return $this->each(function (DOMNode $node, $index) use ($newNode) {
            $newNode = $this->newNode($newNode, $index);

            $nodes = $this->xpathQuery('descendant::*[last()]', $newNode);
            if (!$nodes) {
                throw new Exception('Invalid wrap html format.');
            }

            $deepestNode = end($nodes);

            foreach (iterator_to_array($node->childNodes) as $childNode) {
                $deepestNode->appendChild($childNode);
            }

            $node->appendChild($newNode);
        });
    }

    /**
     * Wrap an HTML structure around all matched nodes.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $content
     *
     * @return static
     */
    public function wrapAll($content)
    {
        $content = $this->contentResolve($content);
        if (!$content->count()) {
            return $this;
        }

        $newNode = $content[0];
        $this->each(function (DOMNode $node, $index) use ($newNode) {
            if ($index === 0) {
                $this->resolve($node)->wrap($newNode);
            } else {
                $this->nodes[0]->parentNode->appendChild($node);
            }
        });

        return $this;
    }

    /**
     * Remove the parents of the matched nodes from the DOM.
     * A optional selector to check the parent node against.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function unwrap(?string $selector = null)
    {
        return $this->parent($selector)->unwrapSelf();
    }

    /**
     * Remove the HTML tag of the matched nodes from the DOM.
     * Leaving the child nodes in their place.
     *
     * @return static
     */
    public function unwrapSelf()
    {
        return $this->each(function (HtmlNode $node) {
            $node->unwrapSelf();
        });
    }

    /**
     * When the selection needs a new node, return the original one or a clone.
     *
     * @param DOMNode $newNode
     * @param int     $index
     *
     * @return DOMNode
     */
    protected function newNode(DOMNode $newNode, int $index)
    {
        return $index !== $this->count() - 1
            ? $newNode->cloneNode(true)
            : $newNode;
    }
}
