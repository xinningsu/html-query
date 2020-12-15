<?php

namespace Sulao\HtmlQuery;

use Closure;
use DOMDocument;
use DOMNode;
use DOMNodeList;

/**
 * Trait Selector
 *
 * @package Sulao\HtmlQuery
 */
trait Selector
{
    use Resolver;

    /**
     * @var DOMDocument
     */
    protected $doc;

    /**
     * @var DOMNode[]
     */
    protected $nodes;

    abstract protected function getClosureClass(Closure $function, int $index);
    abstract protected function closureResolve(string $class, DOMNode $node);

    /**
     *  Make the static object can be called as a function.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static|null
     */
    public function __invoke($selector)
    {
        if (is_string($selector)) {
            return $this->query($selector);
        }

        return $this->resolve($selector);
    }

    /**
     * If the parameter is raw html, then create document fragment for it,
     * If the parameter is a css selector, get the descendants
     * of each current node, filtered by a css selector.
     * If the parameter is selection, resolve that selection
     *
     * @param string $selector css selector or raw html
     *
     * @return static
     */
    public function query(string $selector)
    {
        if (Helper::isRawHtml($selector)) {
            return $this->htmlResolve($selector);
        }

        return $this->targetResolve($selector);
    }

    /**
     * Get the descendants of each matched node, filtered by a selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function find($selector)
    {
        if (is_string($selector)) {
            $selection = $this->xpathResolve(Helper::toXpath($selector));

            return Helper::isIdSelector($selector)
                ? $this->resolve($selection->nodes[0] ?? [])
                : $selection;
        }

        $descendants = $this->xpathResolve('descendant::*');

        return $descendants->intersect($selector);
    }

    /**
     * Get the nodes in the current nodes filtered by a selector.
     *
     * @param string|Closure|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function filter($selector)
    {
        if (is_string($selector)) {
            $xpath = Helper::toXpath($selector, 'self::');
            return $this->xpathResolve($xpath);
        } elseif ($selector instanceof Closure) {
            $class = $this->getClosureClass($selector, 1);

            $nodes = [];
            foreach ($this->nodes as $key => $node) {
                $resolve = $this->closureResolve($class, $node);
                if (!empty($resolve) && $selector($key, $resolve)) {
                    $nodes[] = $node;
                }
            }

            return $this->resolve($nodes);
        }

        return $this->intersect($selector);
    }

    /**
     * Get the parent of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function parent(?string $selector = null)
    {
        $selector = is_null($selector) ? '*' : $selector;
        $xpath = Helper::toXpath($selector, 'parent::');

        return $this->xpathResolve($xpath);
    }

    /**
     * Get the ancestors of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function parents(?string $selector = null)
    {
        $selector = is_null($selector) ? '*' : $selector;
        $xpath = Helper::toXpath($selector, 'ancestor::');

        return $this->xpathResolve($xpath);
    }

    /**
     * Get the ancestors of each node in the current nodes,
     * up to but not including the node matched by the selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function parentsUntil($selector)
    {
        return $this->relationResolve('parentNode', $selector);
    }

    /**
     * Get the children of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function children(?string $selector = null)
    {
        $selector = is_null($selector) ? '*' : $selector;
        $xpath = Helper::toXpath($selector, 'child::');

        return $this->xpathResolve($xpath);
    }

    /**
     * Get the siblings of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function siblings(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "preceding-sibling::{$xpath}|following-sibling::{$xpath}";

        return $this->xpathResolve($xpath);
    }

    /**
     * Get the immediately preceding sibling of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function prev(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "preceding-sibling::{$xpath}[1]";

        return $this->xpathResolve($xpath);
    }

    /**
     * Get all preceding siblings of each node in the current nodes,
     * optionally filtered by a css selector
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function prevAll(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "preceding-sibling::{$xpath}";

        return $this->xpathResolve($xpath);
    }

    /**
     * Get all preceding siblings of each node
     * up to but not including the node matched by the selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function prevUntil($selector)
    {
        return $this->relationResolve('previousSibling', $selector);
    }

    /**
     * Get the immediately following sibling of each node in the current nodes,
     * optionally filtered by a css selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function next(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "following-sibling::{$xpath}[1]";

        return $this->xpathResolve($xpath);
    }

    /**
     * Get all following siblings of each node in the current nodes,
     * optionally filtered by a selector.
     *
     * @param string|null $selector
     *
     * @return static
     */
    public function nextAll(?string $selector = null)
    {
        $xpath = is_null($selector) ? '*' : Helper::toXpath($selector, '');
        $xpath = "following-sibling::{$xpath}";

        return $this->xpathResolve($xpath);
    }

    /**
     * Get all following siblings of each node
     * up to but not including the node matched by the selector.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function nextUntil($selector)
    {
        return $this->relationResolve('nextSibling', $selector);
    }

    /**
     * Create a new static object with nodes added to the current nodes.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function add($selector)
    {
        $nodes = $this->targetResolve($selector)->nodes;

        $nodes = array_merge($this->nodes, $nodes);
        $nodes = Helper::strictArrayUnique($nodes);

        return $this->resolve($nodes);
    }

    /**
     * Create a new static object with the intersected nodes
     * between current nodes and nodes of the selection.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function intersect($selector)
    {
        $selection = $this->targetResolve($selector);

        return $this->resolve(
            Helper::strictArrayIntersect($this->nodes, $selection->nodes)
        );
    }

    /**
     * Remove nodes from the current nodes.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return static
     */
    public function not($selector)
    {
        $nodes = $this->targetResolve($selector)->nodes;
        $nodes = Helper::strictArrayDiff($this->nodes, $nodes);

        return $this->resolve($nodes);
    }

    /**
     * Check the current matched nodes against a selector, node(s), or static
     * object and return true if at least one of nodes matches.
     *
     * @param string|DOMNode|DOMNode[]|DOMNodeList|static $selector
     *
     * @return bool
     */
    public function is($selector): bool
    {
        if (count($this->nodes)) {
            return (bool) count($this->intersect($selector)->nodes);
        }

        return false;
    }
}
